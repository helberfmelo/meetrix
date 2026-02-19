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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'subscription_tier')) {
                $table->string('subscription_tier')->default('free')->after('email');
            }
            if (!Schema::hasColumn('users', 'billing_cycle')) {
                $table->enum('billing_cycle', ['monthly', 'annual'])->nullable()->after('subscription_tier');
            }
            if (!Schema::hasColumn('users', 'stripe_id')) {
                $table->string('stripe_id')->nullable()->index()->after('billing_cycle');
            }
            if (!Schema::hasColumn('users', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('stripe_id');
            }
            if (!Schema::hasColumn('users', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            }
            if (!Schema::hasColumn('users', 'country_code')) {
                $table->string('country_code', 2)->nullable()->after('subscription_ends_at');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'region')) {
                $table->string('region')->default('global')->after('slug');
            }
            if (!Schema::hasColumn('tenants', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('region');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['subscription_tier', 'billing_cycle', 'stripe_id', 'trial_ends_at', 'subscription_ends_at', 'country_code']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['region', 'currency']);
        });
    }
};
