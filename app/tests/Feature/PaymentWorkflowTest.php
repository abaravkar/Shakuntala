<?php

namespace Tests\Feature;

use App\Models\FranchiseApplication;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function applicant(): array
    {
        return ['name' => 'Payment QA', 'mobile' => '9876543210', 'email' => 'qa@example.test', 'city' => 'Pune', 'trade_name' => 'Test shop', 'has_gst' => '0', 'address' => 'Test address', 'shop_ownership' => 'rented', 'shop_area' => 400, 'shop_address' => 'Shop address', 'budget' => '10_25', 'locale' => 'en', 'consent' => 1];
    }

    private function claim(string $reference = 'UTR12345'): array
    {
        return ['payment_method' => 'bank', 'transaction_reference' => $reference, 'transaction_amount' => '5000.00', 'transaction_date' => now()->toDateString(), 'payer_name' => 'Payment QA', 'bank_or_app' => 'Test bank'];
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function signed(string $route, FranchiseApplication $application): string
    {
        return URL::temporarySignedRoute($route, now()->addMinutes(30), ['application' => $application->reference]);
    }

    public function test_optional_maps_and_signed_status_privacy(): void
    {
        $response = $this->post('/franchise/applications', $this->applicant())->assertRedirect()->assertSessionHas('receipt');
        $application = FranchiseApplication::first();
        $this->assertNull($application->map_url);
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Payment &amp; onboarding status', false)->assertSee('Already paid?')->assertSee('data-application-saved', false);
        $this->get($response->headers->get('Location'))->assertOk()->assertDontSee('data-application-saved', false);
        $this->get('/applications/'.$application->reference.'/status')->assertForbidden();
        $this->post('/applications/'.$application->reference.'/payment', $this->claim())->assertForbidden();
        $this->get(URL::temporarySignedRoute('application.status', now()->subMinute(), ['application' => $application->reference]))->assertForbidden();
    }

    public function test_offline_claim_waits_for_admin_and_verified_payment_unlocks_review(): void
    {
        $this->post('/franchise/applications', $this->applicant() + $this->claim() + ['payment_status' => 'verified', 'status' => 'signed'])->assertRedirect();
        $application = FranchiseApplication::first();
        $payment = PaymentAttempt::first();
        $this->assertSame('pending_verification', $application->payment_status);
        $this->assertSame('awaiting_payment_verification', $application->status);
        $this->get($this->signed('application.status', $application))->assertSee('Please wait')->assertDontSee('action="'.$this->signed('application.online', $application).'"', false);
        $this->post('/admin/payments/'.$payment->id.'/review', ['decision' => 'verified', 'review_notes' => 'Bank statement checked'])->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->post('/admin/payments/'.$payment->id.'/review', ['decision' => 'verified', 'review_notes' => 'Bank statement checked'])->assertForbidden();
        $admin = $this->admin();
        $this->actingAs($admin);
        $this->post('/admin/applications/'.$application->id.'/review', ['decision' => 'approved', 'notes' => 'Looks good'])->assertStatus(409);
        $this->post('/admin/payments/'.$payment->id.'/review', ['decision' => 'verified'])->assertSessionHasErrors('review_notes');
        $this->post('/admin/payments/'.$payment->id.'/review', ['decision' => 'verified', 'review_notes' => 'Bank statement checked'])->assertRedirect();
        $this->assertDatabaseHas('franchise_applications', ['id' => $application->id, 'payment_status' => 'verified', 'status' => 'under_review']);
        $this->assertDatabaseHas('payment_attempts', ['id' => $payment->id, 'reviewed_by' => $admin->id, 'status' => 'verified']);
        $this->post('/admin/applications/'.$application->id.'/review', ['decision' => 'approved', 'notes' => 'Business reviewed'])->assertRedirect();
        $this->get($this->signed('application.status', $application))->assertSee('Application approved for the agreement stage');
        $this->assertDatabaseHas('franchise_applications', ['id' => $application->id, 'status' => 'agreement_pending']);
        $this->post('/admin/payments/'.$payment->id.'/review', ['decision' => 'rejected', 'review_notes' => 'Duplicate action'])->assertStatus(409);
    }

    public function test_invalid_amount_missing_fields_and_reused_transactions_cannot_bypass_payment(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->post('/franchise/applications', $this->applicant() + ['payment_method' => 'upi'])->assertSessionHasErrors(['transaction_reference', 'transaction_amount', 'transaction_date', 'payer_name']);
        $claim = $this->claim();
        $claim['transaction_amount'] = '1';
        $this->post('/franchise/applications', $this->applicant() + $claim)->assertSessionHasErrors('transaction_amount');
        $this->assertDatabaseCount('franchise_applications', 0);
        $this->post('/franchise/applications', $this->applicant() + $this->claim())->assertRedirect();
        $this->post('/franchise/applications', $this->applicant() + $this->claim())->assertSessionHasErrors('transaction_reference');
        $this->assertDatabaseCount('payment_attempts', 1);
    }

    public function test_rejected_claim_can_be_corrected_but_pending_or_verified_cannot_be_replaced(): void
    {
        $this->post('/franchise/applications', $this->applicant() + $this->claim());
        $application = FranchiseApplication::first();
        $payment = PaymentAttempt::first();
        $this->post($this->signed('application.payment', $application), $this->claim('SECOND'))->assertStatus(409);
        $this->actingAs($this->admin())->post('/admin/payments/'.$payment->id.'/review', ['decision' => 'rejected', 'review_notes' => 'No matching transfer'])->assertRedirect();
        $this->assertDatabaseHas('franchise_applications', ['payment_status' => 'rejected', 'status' => 'awaiting_payment']);
        $this->post($this->signed('application.payment', $application), $this->claim('CORRECTED'))->assertRedirect();
        $this->assertDatabaseCount('payment_attempts', 2);
        $this->assertDatabaseHas('application_events', ['event' => 'payment_rejected']);
    }

    public function test_proofs_and_payment_settings_require_admin_and_accept_safe_files(): void
    {
        Storage::fake('local');
        $this->post('/franchise/applications', $this->applicant() + $this->claim() + ['payment_proof' => UploadedFile::fake()->create('receipt.pdf', 20, 'application/pdf')])->assertRedirect();
        $payment = PaymentAttempt::first();
        Storage::disk('local')->assertExists($payment->proof_path);
        $this->get('/admin/payments/'.$payment->id.'/proof')->assertRedirect('/admin/login');
        $this->get('/admin/payment-settings')->assertRedirect('/admin/login');
        $this->actingAs($this->admin())->get('/admin/payments/'.$payment->id.'/proof')->assertDownload();
        $this->post('/admin/payment-settings', ['razorpay_url' => 'https://attacker.example/pay'])->assertSessionHasErrors('razorpay_url');
        $this->post('/admin/payment-settings', ['account_name' => 'Test business', 'bank_name' => 'Test bank', 'account_number' => 'TEST-ONLY', 'ifsc' => 'TEST0123456', 'upi_id' => 'test@example', 'razorpay_url' => 'https://rzp.io/test-link'])->assertRedirect();
        $this->get('/franchise/application')->assertSee('TEST-ONLY')->assertSee('https://rzp.io/test-link');
    }

    public function test_online_links_are_application_bound_and_reused_without_marking_paid(): void
    {
        config(['payments.razorpay_key_id' => 'test-key', 'payments.razorpay_key_secret' => 'test-secret']);
        $this->post('/franchise/applications', $this->applicant());
        $application = FranchiseApplication::first();
        Http::fake(['api.razorpay.com/v1/payment_links*' => function ($request) {
            return Http::response($request->method() === 'GET' ? ['items' => []] : ['id' => 'plink_TEST123', 'short_url' => 'https://rzp.io/TEST123', 'amount' => 500000, 'currency' => 'INR']);
        }]);
        $this->post($this->signed('application.online', $application))->assertRedirect('https://rzp.io/TEST123');
        $this->post($this->signed('application.online', $application))->assertRedirect('https://rzp.io/TEST123');
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => $r->method() === 'POST' && $r['amount'] === 500000 && $r['reference_id'] === $application->reference && $r['accept_partial'] === false);
        $this->assertDatabaseHas('franchise_applications', ['id' => $application->id, 'payment_status' => 'pending']);
    }

    public function test_signed_webhook_checks_amount_and_reference_and_is_idempotent(): void
    {
        config(['payments.razorpay_webhook_secret' => 'test-secret']);
        $this->post('/franchise/applications', $this->applicant());
        $application = FranchiseApplication::first();
        $application->update(['razorpay_link_id' => 'plink_TEST']);
        $payload = ['event' => 'payment_link.paid', 'payload' => ['payment_link' => ['entity' => ['id' => 'plink_TEST', 'reference_id' => $application->reference, 'status' => 'paid', 'amount' => 500000, 'amount_paid' => 500000, 'currency' => 'INR']], 'payment' => ['entity' => ['id' => 'pay_TEST', 'status' => 'captured', 'amount' => 500000, 'currency' => 'INR']]]];
        $send = function ($body, $signature = null) {
            $json = json_encode($body);

            return $this->call('POST', '/payments/razorpay/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_RAZORPAY_SIGNATURE' => $signature ?? hash_hmac('sha256', $json, 'test-secret')], $json);
        };
        $send($payload, 'forged')->assertUnauthorized();
        $bad = $payload;
        $bad['payload']['payment']['entity']['amount'] = 1;
        $send($bad)->assertStatus(422);
        $bad = $payload;
        $bad['payload']['payment_link']['entity']['reference_id'] = 'wrong';
        $send($bad)->assertStatus(422);
        $send($payload)->assertOk();
        $send($payload)->assertOk();
        $this->assertDatabaseCount('payment_attempts',1);
        $this->assertDatabaseHas('franchise_applications',['payment_status' => 'verified', 'status' => 'under_review']);
        $this->assertDatabaseCount('application_events',2);
    }
}
