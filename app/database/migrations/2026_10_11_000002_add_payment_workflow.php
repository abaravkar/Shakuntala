<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('franchise_applications', function (Blueprint $t) {
            $t->string('map_url', 500)->nullable()->change();
            $t->string('razorpay_link_id')->nullable()->unique();
            $t->string('razorpay_link_url', 500)->nullable();
            $t->timestamp('payment_verified_at')->nullable();
        });
        Schema::create('payment_settings', function (Blueprint $t) {
            $t->id();
            $t->json('details');
            $t->timestamps();
        });
        Schema::create('payment_attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('franchise_application_id')->constrained()->cascadeOnDelete();
            $t->string('method', 20);
            $t->string('transaction_reference', 120)->unique();
            $t->unsignedInteger('amount_paise');
            $t->date('transaction_date');
            $t->string('payer_name', 120);
            $t->string('bank_or_app', 120)->nullable();
            $t->text('notes')->nullable();
            $t->string('proof_path')->nullable();
            $t->string('status', 30)->default('pending_verification');
            $t->foreignId('reviewed_by')->nullable()->constrained('users');
            $t->timestamp('reviewed_at')->nullable();
            $t->text('review_notes')->nullable();
            $t->timestamps();
        });
        Schema::create('application_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('franchise_application_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained();
            $t->string('event', 60);
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_events');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payment_settings');
        Schema::table('franchise_applications', fn (Blueprint $t) => $t->dropColumn(['razorpay_link_id', 'razorpay_link_url', 'payment_verified_at']));
    }
};
