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
                'discount_percent' => 100.00,
                'max_uses' => 10,
                'uses' => 0,
                'expires_at' => '2026-02-20',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        \App\Models\Coupon::where('code', 'cupom100')->delete();
    }
};
