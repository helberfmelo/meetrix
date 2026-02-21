<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FX conversion (currency -> BRL)
    |--------------------------------------------------------------------------
    |
    | Financial KPIs for PR-06 normalize revenue into BRL so product and
    | finance dashboards can compare regions in a single baseline.
    |
    */
    'currency_to_brl' => [
        'BRL' => (float) env('ANALYTICS_FX_BRL_PER_BRL', 1),
        'USD' => (float) env('ANALYTICS_FX_BRL_PER_USD', 5),
        'EUR' => (float) env('ANALYTICS_FX_BRL_PER_EUR', 5.4),
    ],

    /*
    |--------------------------------------------------------------------------
    | Financial KPI hardening controls
    |--------------------------------------------------------------------------
    |
    | PR-07: cache expensive KPI aggregations and cap country breakdown size
    | to keep admin dashboards responsive and predictable under high volume.
    |
    */
    'kpi_cache_ttl_seconds' => (int) env('ANALYTICS_KPI_CACHE_TTL_SECONDS', 60),
    'kpi_country_limit' => (int) env('ANALYTICS_KPI_COUNTRY_LIMIT', 25),
];
