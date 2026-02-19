<?php

namespace App\Services;

class PricingService
{

    /**
     * Get the adjusted price based on Purchasing Power Parity (PPP).
     * Formula: BasePrice * (RegionalMinWage / USMinWage)
     * 
     * @param float $basePriceInUSD
     * @param string $currency (BRL, EUR, USD)
     * @return float
     */
    public function getAdjustedPrice(float $basePriceInUSD, string $currency): float
    {
        $currency = strtoupper($currency);
        $wages = config('pricing.wages');
        
        if (!isset($wages[$currency])) {
            return $basePriceInUSD;
        }

        $regionalWage = $wages[$currency];
        $usWage = $wages['USD'];

        // Scale factor: how much the regional wage is compared to US wage
        $scaleFactor = $regionalWage / $usWage;

        // Apply scale factor to price
        return round($basePriceInUSD * $scaleFactor, 2);
    }

    /**
     * Get regional constants for frontend/metadata.
     */
    public function getRegionalData(): array
    {
        return self::MIN_WAGES;
    }
}
