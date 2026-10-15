<?php

namespace App\Http\Controllers;

use App\Models\ContentItem;
use App\Models\FranchiseApplication;
use App\Models\PaymentAttempt;
use App\Models\PaymentSetting;
use App\Services\ApplicationPayments;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function status(Request $request, FranchiseApplication $application)
    {
        $locale = $application->locale;
        $page = 'application-status';
        $items = ContentItem::orderBy('sort_order')->get();
        $paymentSettings = PaymentSetting::details();

        return response()->view('pages.application-status', compact('application', 'locale', 'page', 'items', 'paymentSettings'))->header('Cache-Control', 'private, no-store');
    }

    public function submit(Request $request, FranchiseApplication $application)
    {
        $request->validate(ApplicationPayments::rules());
        abort_unless(in_array($request->input('payment_method'), ['bank', 'upi', 'qr', 'razorpay']), 422);
        DB::transaction(function () use ($request, $application) {
            $locked = FranchiseApplication::lockForUpdate()->findOrFail($application->id);
            abort_if($locked->payment_status === 'verified' || $locked->payments()->where('status', 'pending_verification')->exists(), 409, 'A payment is already verified or awaiting review.');
            ApplicationPayments::record($locked, $request);
            ApplicationPayments::event($locked, 'payment_submitted');
        });

        return back()->with('message', 'Transaction submitted. Please wait for administrator verification.');
    }

    public function online(Request $request, FranchiseApplication $application)
    {
        abort_unless(config('payments.razorpay_key_id') && config('payments.razorpay_key_secret'), 503, 'Razorpay credentials are not configured.');
        try {
            DB::transaction(function () use ($application) {
                $locked = FranchiseApplication::lockForUpdate()->findOrFail($application->id);
                abort_if($locked->payment_status === 'verified' || $locked->payments()->where('status', 'pending_verification')->exists(), 409);
                if ($locked->razorpay_link_id) {
                    return;
                }
                // Recover a link after an interrupted earlier request instead of charging twice.
                $client = Http::withBasicAuth(config('payments.razorpay_key_id'), config('payments.razorpay_key_secret'))->acceptJson()->timeout(20);
                $existing = $client->get('https://api.razorpay.com/v1/payment_links', ['reference_id' => $locked->reference])->throw()->json('items', []);
                $link = collect($existing)->firstWhere('reference_id', $locked->reference);
                if (! $link) {
                    $link = $client->post('https://api.razorpay.com/v1/payment_links', [
                        'amount' => $locked->fee_paise, 'currency' => 'INR', 'accept_partial' => false, 'reference_id' => $locked->reference,
                        'description' => 'Shakuntala franchise application fee', 'notify' => ['sms' => false, 'email' => false],
                        'notes' => ['application_reference' => $locked->reference],
                    ])->throw()->json();
                }
                abort_unless(($link['amount'] ?? 0) === $locked->fee_paise && ($link['currency'] ?? '') === 'INR' && preg_match('/^plink_[A-Za-z0-9]+$/', $link['id'] ?? ''), 502);
                $host = parse_url($link['short_url'] ?? '', PHP_URL_HOST);
                abort_unless(parse_url($link['short_url'] ?? '', PHP_URL_SCHEME) === 'https' && in_array($host, ['rzp.io', 'rzp.me', 'razorpay.com']), 502);
                $locked->update(['razorpay_link_id' => $link['id'], 'razorpay_link_url' => $link['short_url']]);
                ApplicationPayments::event($locked, 'online_link_created');
            });
        } catch (RequestException|ConnectionException $e) {
            return back()->withErrors(['payment' => 'Could not create the Razorpay link. Your application is saved; please retry later.']);
        }

        return redirect()->away($application->fresh()->razorpay_link_url);
    }

    public function webhook(Request $request)
    {
        $secret = config('payments.razorpay_webhook_secret');
        abort_unless($secret, 503);
        abort_unless(hash_equals(hash_hmac('sha256', $request->getContent(), $secret), $request->header('X-Razorpay-Signature', '')), 401);
        if ($request->input('event') !== 'payment_link.paid') {
            return response()->json(['received' => true]);
        }
        $link = $request->input('payload.payment_link.entity', []);
        $payment = $request->input('payload.payment.entity', []);
        $application = FranchiseApplication::where('razorpay_link_id', $link['id'] ?? '')->first();
        if (! $application) {
            return response()->json(['error' => 'Unmatched payment link'], 422);
        }
        abort_unless(($link['reference_id'] ?? null) === $application->reference && ($link['status'] ?? '') === 'paid' && ($link['amount'] ?? 0) === $application->fee_paise && ($link['amount_paid'] ?? 0) === $application->fee_paise && ($link['currency'] ?? '') === 'INR' && ($payment['status'] ?? '') === 'captured' && ($payment['amount'] ?? 0) === $application->fee_paise && ($payment['currency'] ?? '') === 'INR' && preg_match('/^pay_[A-Za-z0-9]+$/', $payment['id'] ?? ''), 422);
        DB::transaction(function () use ($application, $payment) {
            $locked = FranchiseApplication::lockForUpdate()->findOrFail($application->id);
            if ($locked->payment_status === 'verified') {
                return;
            }
            $attempt = PaymentAttempt::where('transaction_reference', $payment['id'])->first();
            abort_if($attempt && $attempt->franchise_application_id !== $locked->id, 409);
            $attempt ??= $locked->payments()->create(['method' => 'razorpay', 'transaction_reference' => $payment['id'], 'amount_paise' => $locked->fee_paise, 'transaction_date' => now()->toDateString(), 'payer_name' => $locked->name]);
            ApplicationPayments::verify($locked, $attempt, null, 'Verified Razorpay payment_link.paid webhook.');
        });

        return response()->json(['received' => true]);
    }

    public function review(Request $request, PaymentAttempt $payment)
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['verified', 'rejected'])], 'review_notes' => 'required|string|max:2000']);
        DB::transaction(function () use ($request, $payment, $data) {
            $application = FranchiseApplication::lockForUpdate()->findOrFail($payment->franchise_application_id);
            $attempt = PaymentAttempt::lockForUpdate()->findOrFail($payment->id);
            abort_unless($attempt->status === 'pending_verification' && $application->payment_status !== 'verified', 409);
            if ($data['decision'] === 'verified') {
                abort_unless($attempt->amount_paise === $application->fee_paise, 422);
                ApplicationPayments::verify($application, $attempt, $request->user()->id, $data['review_notes']);
            } else {
                $attempt->update(['status' => 'rejected', 'reviewed_at' => now(), 'reviewed_by' => $request->user()->id, 'review_notes' => $data['review_notes']]);
                $application->update(['payment_status' => 'rejected', 'status' => 'awaiting_payment']);
                ApplicationPayments::event($application, 'payment_rejected', $request->user()->id, $data['review_notes']);
            }
        });

        return back()->with('message', 'Payment reviewed.');
    }

    public function advance(Request $request, FranchiseApplication $application)
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'rejected'])], 'notes' => 'required|string|max:2000']);
        DB::transaction(function () use ($application, $request, $data) {
            $locked = FranchiseApplication::lockForUpdate()->findOrFail($application->id);
            abort_unless($locked->payment_status === 'verified' && $locked->status === 'under_review', 409, 'Verify payment before reviewing the application.');
            $locked->update(['status' => $data['decision'] === 'approved' ? 'agreement_pending' : 'application_rejected']);
            ApplicationPayments::event($locked, 'application_'.$data['decision'], $request->user()->id, $data['notes']);
        });

        return back()->with('message', 'Application review saved.');
    }

    public function proof(PaymentAttempt $payment)
    {
        abort_unless($payment->proof_path && Storage::disk('local')->exists($payment->proof_path), 404);

        return Storage::disk('local')->download($payment->proof_path, 'transaction-proof.'.pathinfo($payment->proof_path, PATHINFO_EXTENSION), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function settings()
    {
        return view('admin.payment-settings', ['details' => PaymentSetting::details()]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate(['account_name' => 'nullable|string|max:160', 'bank_name' => 'nullable|string|max:160', 'account_number' => 'nullable|string|max:40', 'ifsc' => 'nullable|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/', 'upi_id' => 'nullable|string|max:120', 'razorpay_url' => ['nullable', 'url:https', 'max:500', function ($a, $v, $fail) {
            if (! in_array(parse_url($v, PHP_URL_HOST), ['rzp.io', 'rzp.me', 'razorpay.com', 'pages.razorpay.com'])) {
                $fail('Use an official Razorpay payment link.');
            }
        }], 'qr' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048']);
        unset($data['qr']);
        $existing = PaymentSetting::details();
        if ($request->hasFile('qr')) {
            $data['qr_path'] = $request->file('qr')->store('payment-qr', 'local');
        }
        PaymentSetting::updateOrCreate(['id' => 1], ['details' => array_merge($existing, $data)]);

        return back()->with('message', 'Payment instructions saved.');
    }

    public function qr()
    {
        $path = PaymentSetting::details()['qr_path'] ?? null;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), ['X-Content-Type-Options' => 'nosniff']);
    }
}
