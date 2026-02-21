<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payments Feature Flag
    |--------------------------------------------------------------------------
    |
    | Global toggle for customer-facing payment flows. Keep disabled on initial
    | deploy and allow rollout by account id in small controlled batches.
    |
    */
    'enabled' => filter_var(env('PAYMENTS_ENABLED', false), FILTER_VALIDATE_BOOL),

    'rollout_user_ids' => array_values(array_filter(array_map(
        static fn (string $value): int => (int) trim($value),
        explode(',', (string) env('PAYMENTS_ROLLOUT_USER_IDS', ''))
    ))),

    'provider' => 'stripe',

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'connect_refresh_url' => env('STRIPE_CONNECT_REFRESH_URL'),
        'connect_return_url' => env('STRIPE_CONNECT_RETURN_URL'),
    ],
];

