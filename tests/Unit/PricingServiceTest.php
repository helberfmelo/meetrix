<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PricingService;

class PricingServiceTest extends TestCase
{
    /**
     * Test PPP calculation logic.
     */
    public function test_ppp_calculation_logic()
    {
        $service = new PricingService();
        $basePrice = 100.00;

        // USD (Baseline)
        $this->assertEquals(100.00, $service->getAdjustedPrice($basePrice, 'USD'));

        // BRL Calculation: 100 * (1412 / 2400) = 58.833...
        $this->assertEquals(58.83, $service->getAdjustedPrice($basePrice, 'BRL'));

        // EUR Calculation: 100 * (1800 / 2400) = 75.00
        $this->assertEquals(75.00, $service->getAdjustedPrice($basePrice, 'EUR'));

        // Unknown Currency (Fallback to base)
        $this->assertEquals(100.00, $service->getAdjustedPrice($basePrice, 'GPB'));
    }
}
