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
        \App\Models\Coupon::updateOrCreate(
            ['code' => 'cupom100'],
            [
                'discount_type' => 'percent',
                'discount_value' => 100,
                'is_active' => true,
                'expires_at' => now()->addYear(),
            ]
        );
    }

    public function down(): void
    {
        \App\Models\Coupon::where('code', 'cupom100')->delete();
    }
};
