<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('connected_accounts')) {
            Schema::create('connected_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('provider', 32)->default('stripe_connect');
                $table->string('provider_account_id')->nullable();
                $table->string('status', 32)->default('pending');
                $table->boolean('charges_enabled')->default(false);
                $table->boolean('payouts_enabled')->default(false);
                $table->boolean('details_submitted')->default(false);
                $table->json('capabilities')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['provider', 'provider_account_id']);
                $table->index(['user_id', 'status']);
            });
        }

        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('plan_code', 64)->default('free');
                $table->string('billing_cycle', 16)->default('monthly');
                $table->string('account_mode', 32)->default('scheduling_only');
                $table->string('provider', 32)->default('stripe');
                $table->string('provider_subscription_id')->nullable()->unique();
                $table->decimal('price', 10, 2)->default(0);
                $table->string('currency', 3)->default('BRL');
                $table->string('status', 32)->default('inactive');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('current_period_start')->nullable();
                $table->timestamp('current_period_end')->nullable();
                $table->timestamp('canceled_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }

        if (!Schema::hasTable('payment_intents')) {
            Schema::create('payment_intents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
                $table->string('provider', 32)->default('stripe');
                $table->string('provider_intent_id')->unique();
                $table->string('idempotency_key')->nullable()->unique();
                $table->decimal('amount', 10, 2);
                $table->string('currency', 3)->default('BRL');
                $table->string('status', 32)->default('requires_payment_method');
                $table->string('last_error_code', 64)->nullable();
                $table->text('last_error_message')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('canceled_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
                $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
                $table->string('provider', 32)->default('stripe');
                $table->string('provider_payment_id')->nullable()->unique();
                $table->string('provider_intent_id')->nullable()->index();
                $table->decimal('amount', 10, 2);
                $table->string('currency', 3)->default('BRL');
                $table->decimal('platform_fee_percent', 5, 2)->default(0);
                $table->decimal('platform_fee_amount', 10, 2)->default(0);
                $table->decimal('net_amount', 10, 2)->default(0);
                $table->string('status', 32)->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamp('refunded_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }

        if (!Schema::hasTable('transfers')) {
            Schema::create('transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
                $table->foreignId('connected_account_id')->nullable()->constrained('connected_accounts')->nullOnDelete();
                $table->string('provider', 32)->default('stripe');
                $table->string('provider_transfer_id')->nullable()->unique();
                $table->decimal('amount', 10, 2);
                $table->string('currency', 3)->default('BRL');
                $table->string('status', 32)->default('pending');
                $table->timestamp('transferred_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }

        if (!Schema::hasTable('payouts')) {
            Schema::create('payouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('connected_account_id')->nullable()->constrained('connected_accounts')->nullOnDelete();
                $table->string('provider', 32)->default('stripe');
                $table->string('provider_payout_id')->nullable()->unique();
                $table->decimal('amount', 10, 2);
                $table->string('currency', 3)->default('BRL');
                $table->string('status', 32)->default('pending');
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('paid_out_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }

        if (!Schema::hasTable('geo_pricing')) {
            Schema::create('geo_pricing', function (Blueprint $table) {
                $table->id();
                $table->string('region_code', 16);
                $table->string('account_mode', 32)->default('scheduling_with_payments');
                $table->string('currency', 3);
                $table->decimal('monthly_price', 10, 2);
                $table->decimal('annual_price', 10, 2);
                $table->decimal('platform_fee_percent', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['region_code', 'account_mode']);
                $table->index(['currency', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_pricing');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_intents');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('connected_accounts');
    }
};
