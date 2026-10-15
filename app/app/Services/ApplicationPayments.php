<?php

namespace App\Services;

use App\Models\FranchiseApplication;
use App\Models\PaymentAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ApplicationPayments
{
    public static function rules(): array
    {
        return [
            'payment_method' => ['nullable', Rule::in(['later', 'bank', 'upi', 'qr', 'online', 'razorpay'])],
            'transaction_reference' => ['nullable', 'required_if:payment_method,bank,upi,qr,razorpay', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('payment_attempts')],
            'transaction_amount' => ['nullable', 'required_if:payment_method,bank,upi,qr,razorpay', 'numeric', 'min:0.01', 'max:1000000', 'decimal:0,2'],
            'transaction_date' => ['nullable', 'required_if:payment_method,bank,upi,qr,razorpay', 'date', 'before_or_equal:today'],
            'payer_name' => ['nullable', 'required_if:payment_method,bank,upi,qr,razorpay', 'string', 'max:120'],
            'bank_or_app' => 'nullable|string|max:120', 'payment_notes' => 'nullable|string|max:2000',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public static function record(FranchiseApplication $application, Request $request): void
    {
        if (! in_array($request->input('payment_method'), ['bank', 'upi', 'qr', 'razorpay'])) {
            return;
        }
        $amount = (int) round((float) $request->input('transaction_amount') * 100);
        if ($amount !== $application->fee_paise) {
            throw ValidationException::withMessages(['transaction_amount' => 'Enter the exact application fee amount.']);
        }
        $path = $request->file('payment_proof')?->store('payment-proofs', 'local');
        try {
            $application->payments()->create([
                'method' => $request->input('payment_method'), 'transaction_reference' => $request->input('transaction_reference'),
                'amount_paise' => $amount, 'transaction_date' => $request->input('transaction_date'), 'payer_name' => $request->input('payer_name'),
                'bank_or_app' => $request->input('bank_or_app'), 'notes' => $request->input('payment_notes'), 'proof_path' => $path,
            ]);
            $application->update(['payment_status' => 'pending_verification', 'status' => 'awaiting_payment_verification']);
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            } throw $e;
        }
    }

    public static function event(FranchiseApplication $application, string $event, ?int $userId = null, ?string $notes = null): void
    {
        DB::table('application_events')->insert(['franchise_application_id' => $application->id, 'user_id' => $userId, 'event' => $event, 'notes' => $notes, 'created_at' => now(), 'updated_at' => now()]);
    }

    public static function verify(FranchiseApplication $application, PaymentAttempt $payment, ?int $userId, string $notes): void
    {
        $payment->update(['status' => 'verified', 'reviewed_by' => $userId, 'reviewed_at' => now(), 'review_notes' => $notes]);
        $application->update(['payment_status' => 'verified', 'payment_verified_at' => now(), 'status' => 'under_review']);
        static::event($application, 'payment_verified', $userId, $notes);
    }
}
