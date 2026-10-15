<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->boolean('is_admin')->default(false));
        Schema::create('content_items', function (Blueprint $t) {
            $t->id();
            $t->string('type', 20)->index();
            $t->string('slug')->unique();
            $t->json('title');
            $t->json('body');
            $t->string('image')->nullable();
            $t->unsignedInteger('price')->nullable();
            $t->string('status', 30)->default('draft');
            $t->string('url', 500)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
        Schema::create('inquiries', function (Blueprint $t) {
            $t->id();
            $t->uuid('reference')->unique();
            $t->string('kind', 20);
            $t->string('name', 120);
            $t->string('mobile', 20);
            $t->string('email')->nullable();
            $t->string('city', 120)->nullable();
            $t->string('budget', 50)->nullable();
            $t->text('message')->nullable();
            $t->string('locale', 2);
            $t->timestamp('consented_at');
            $t->string('status', 30)->default('new');
            $t->timestamps();
        });
        Schema::create('franchise_applications', function (Blueprint $t) {
            $t->id();
            $t->uuid('reference')->unique();
            $t->string('name', 120);
            $t->string('mobile', 20);
            $t->string('email');
            $t->string('city', 120);
            $t->string('company_name', 160)->nullable();
            $t->string('trade_name', 160);
            $t->boolean('has_gst');
            $t->string('gst_number', 15)->nullable();
            $t->text('address');
            $t->string('shop_ownership', 20);
            $t->unsignedInteger('shop_area');
            $t->text('shop_address');
            $t->string('map_url', 500);
            $t->string('budget', 50);
            $t->text('experience')->nullable();
            $t->string('locale', 2);
            $t->timestamp('consented_at');
            $t->unsignedInteger('fee_paise')->default(500000);
            $t->string('payment_status', 30)->default('pending');
            $t->string('status', 30)->default('awaiting_payment');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('franchise_applications');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('content_items');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_admin'));
    }
};
