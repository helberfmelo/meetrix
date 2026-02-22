<?php

namespace Tests\Unit;

use App\Models\PricingOperationalFee;
use App\Models\PricingPlatformCommission;
use App\Services\PricingFeeEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingFeeEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_returns_commission_operational_and_total_fee_percentages(): void
    {
        PricingPlatformCommission::create([
            'plan_code' => 'payments_pro',
            'currency' => 'BRL',
            'payment_method' => 'card',
            'commission_percent' => 2.50,
            'is_active' => true,
        ]);

        PricingOperationalFee::create([
            'currency' => 'BRL',
            'payment_method' => 'card',
            'fee_percent' => 3.49,
            'is_active' => true,
        ]);

        $composition = app(PricingFeeEngineService::class)->calculate('payments_pro', 'BRL', 'card', 100);

        $this->assertSame(2.5, (float) $composition['commission_percent']);
        $this->assertSame(3.49, (float) $composition['operational_fee_percent']);
        $this->assertSame(5.99, (float) $composition['total_fee_percent']);
        $this->assertSame(5.99, (float) $composition['effective_total_fee_percent']);
        $this->assertSame(5.99, (float) $composition['total_fee_amount']);
        $this->assertSame(94.01, (float) $composition['net_amount']);
    }

    public function test_calculate_respects_active_flags_for_effective_totals(): void
    {
        PricingPlatformCommission::create([
            'plan_code' => 'payments_pro',
            'currency' => 'USD',
            'payment_method' => 'card',
            'commission_percent' => 1.25,
            'is_active' => false,
        ]);

        PricingOperationalFee::create([
            'currency' => 'USD',
            'payment_method' => 'card',
            'fee_percent' => 2.90,
            'is_active' => true,
        ]);

        $composition = app(PricingFeeEngineService::class)->calculate('payments_pro', 'USD', 'card', 100);

        $this->assertSame(4.15, (float) $composition['total_fee_percent']);
        $this->assertSame(2.9, (float) $composition['effective_total_fee_percent']);
        $this->assertFalse((bool) $composition['commission_active']);
        $this->assertTrue((bool) $composition['operational_fee_active']);
        $this->assertSame(2.9, (float) $composition['total_fee_amount']);
    }
}

