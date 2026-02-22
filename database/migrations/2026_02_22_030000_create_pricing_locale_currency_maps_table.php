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
        if (!Schema::hasTable('pricing_locale_currency_maps')) {
            Schema::create('pricing_locale_currency_maps', function (Blueprint $table) {
                $table->id();
                $table->string('locale_code', 16)->unique();
                $table->string('region_code', 16);
                $table->string('currency', 3);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['currency', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_locale_currency_maps');
    }
};
