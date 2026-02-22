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
        if (!Schema::hasTable('pricing_platform_commissions')) {
            Schema::create('pricing_platform_commissions', function (Blueprint $table) {
                $table->id();
                $table->string('plan_code', 64);
                $table->string('currency', 3);
                $table->string('payment_method', 32);
                $table->decimal('commission_percent', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['plan_code', 'currency', 'payment_method'], 'pricing_platform_commission_unique');
                $table->index(['currency', 'payment_method', 'is_active'], 'pricing_platform_commission_lookup');
            });
        }

        if (!Schema::hasTable('pricing_operational_fees')) {
            Schema::create('pricing_operational_fees', function (Blueprint $table) {
                $table->id();
                $table->string('currency', 3);
                $table->string('payment_method', 32);
                $table->decimal('fee_percent', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['currency', 'payment_method'], 'pricing_operational_fee_unique');
                $table->index(['currency', 'payment_method', 'is_active'], 'pricing_operational_fee_lookup');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_operational_fees');
        Schema::dropIfExists('pricing_platform_commissions');
    }
};

