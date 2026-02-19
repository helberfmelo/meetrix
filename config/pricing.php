<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Regional Minimum Wages (PPP)
    |--------------------------------------------------------------------------
    |
    | These values represent the monthly minimum wage in each region.
    | Formula: FinalPrice = BasePrice * (UserRegionWage / USBaselineWage)
    |
    */
    'wages' => [
        'USD' => 2400.00, // Baseline
        'BRL' => 1412.00,
        'EUR' => 1800.00,
    ],

    /*
    |--------------------------------------------------------------------------
    | GeoIP Settings
    |--------------------------------------------------------------------------
    */
    'geoip_enabled' => env('GEOIP_ENABLED', true),
];
