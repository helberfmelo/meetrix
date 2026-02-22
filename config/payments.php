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

    'checkout' => [
        'payment_methods_by_currency' => [
            'BRL' => array_values(array_filter(array_map(
                static fn (string $value): string => strtolower(trim($value)),
                explode(',', (string) env('PAYMENTS_METHODS_BRL', 'card'))
            ))),
            'USD' => array_values(array_filter(array_map(
                static fn (string $value): string => strtolower(trim($value)),
                explode(',', (string) env('PAYMENTS_METHODS_USD', 'card'))
            ))),
            'EUR' => array_values(array_filter(array_map(
                static fn (string $value): string => strtolower(trim($value)),
                explode(',', (string) env('PAYMENTS_METHODS_EUR', 'card'))
            ))),
        ],
        'pix' => [
            // Conservative defaults: pix disabled until Sprint 2 validation gates are approved.
            'enabled' => filter_var(env('PAYMENTS_PIX_ENABLED', false), FILTER_VALIDATE_BOOL),
            'split_enabled' => filter_var(env('PAYMENTS_PIX_SPLIT_ENABLED', false), FILTER_VALIDATE_BOOL),
            // Hard lock for production rollout. Keep true until G0 moves to GO with explicit approval.
            'production_lock' => filter_var(env('PAYMENTS_PIX_PRODUCTION_LOCK', true), FILTER_VALIDATE_BOOL),
            'requires_payments_mode' => true,
        ],
    ],
];
