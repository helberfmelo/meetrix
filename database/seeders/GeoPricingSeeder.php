<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GeoPricingSeeder extends Seeder
{
    /**
     * Seed the application's geo_pricing matrix.
     */
    public function run(): void
    {
        $now = now();

        $rows = [
            [
                'region_code' => 'BR',
                'account_mode' => 'scheduling_only',
                'currency' => 'BRL',
                'monthly_price' => 29.00,
                'annual_price' => 23.00,
                'platform_fee_percent' => 0.00,
                'metadata' => json_encode([
                    'label' => 'Brasil',
                    'plan_code' => 'schedule_starter',
                ]),
            ],
            [
                'region_code' => 'USD',
                'account_mode' => 'scheduling_only',
                'currency' => 'USD',
                'monthly_price' => 7.00,
                'annual_price' => 6.00,
                'platform_fee_percent' => 0.00,
                'metadata' => json_encode([
                    'label' => 'US/CA/AU',
                    'plan_code' => 'schedule_starter',
                ]),
            ],
            [
                'region_code' => 'EUR',
                'account_mode' => 'scheduling_only',
                'currency' => 'EUR',
                'monthly_price' => 7.00,
                'annual_price' => 6.00,
                'platform_fee_percent' => 0.00,
                'metadata' => json_encode([
                    'label' => 'Europa e demais',
                    'plan_code' => 'schedule_starter',
                ]),
            ],
            [
                'region_code' => 'BR',
                'account_mode' => 'scheduling_with_payments',
                'currency' => 'BRL',
                'monthly_price' => 39.00,
                'annual_price' => 31.00,
                'platform_fee_percent' => 2.50,
                'metadata' => json_encode([
                    'label' => 'Brasil',
                    'plan_code' => 'payments_pro',
                    'premium_price' => 79,
                    'premium_fee_percent' => 1.50,
                ]),
            ],
            [
                'region_code' => 'USD',
                'account_mode' => 'scheduling_with_payments',
                'currency' => 'USD',
                'monthly_price' => 9.00,
                'annual_price' => 7.00,
                'platform_fee_percent' => 1.25,
                'metadata' => json_encode([
                    'label' => 'US/CA/AU',
                    'plan_code' => 'payments_pro',
                    'premium_price' => 19,
                    'premium_fee_percent' => 0.75,
                ]),
            ],
            [
                'region_code' => 'EUR',
                'account_mode' => 'scheduling_with_payments',
                'currency' => 'EUR',
                'monthly_price' => 9.00,
                'annual_price' => 7.00,
                'platform_fee_percent' => 1.25,
                'metadata' => json_encode([
                    'label' => 'Europa e demais',
                    'plan_code' => 'payments_pro',
                    'premium_price' => 19,
                    'premium_fee_percent' => 0.75,
                ]),
            ],
        ];

        foreach ($rows as $row) {
            DB::table('geo_pricing')->updateOrInsert(
                [
                    'region_code' => $row['region_code'],
                    'account_mode' => $row['account_mode'],
                ],
                [
                    'currency' => $row['currency'],
                    'monthly_price' => $row['monthly_price'],
                    'annual_price' => $row['annual_price'],
                    'platform_fee_percent' => $row['platform_fee_percent'],
                    'is_active' => true,
                    'metadata' => $row['metadata'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        if (Schema::hasTable('pricing_locale_currency_maps')) {
            $localeMappings = [
                ['locale_code' => 'pt-br', 'region_code' => 'BR', 'currency' => 'BRL'],
                ['locale_code' => 'en', 'region_code' => 'USD', 'currency' => 'USD'],
                ['locale_code' => 'en-us', 'region_code' => 'USD', 'currency' => 'USD'],
                ['locale_code' => 'es', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'fr', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'de', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'pt', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'zh', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'zh-cn', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'ja', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'ko', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'it', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'ru', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'tr', 'region_code' => 'EUR', 'currency' => 'EUR'],
                ['locale_code' => 'ar', 'region_code' => 'EUR', 'currency' => 'EUR'],
            ];

            foreach ($localeMappings as $mapping) {
                DB::table('pricing_locale_currency_maps')->updateOrInsert(
                    ['locale_code' => $mapping['locale_code']],
                    [
                        'region_code' => $mapping['region_code'],
                        'currency' => $mapping['currency'],
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        if (Schema::hasTable('pricing_operational_fees')) {
            $operationalFees = [
                ['currency' => 'BRL', 'payment_method' => 'card', 'fee_percent' => 3.49],
                ['currency' => 'BRL', 'payment_method' => 'pix', 'fee_percent' => 0.99],
                ['currency' => 'USD', 'payment_method' => 'card', 'fee_percent' => 2.90],
                ['currency' => 'EUR', 'payment_method' => 'card', 'fee_percent' => 2.90],
            ];

            foreach ($operationalFees as $feeRow) {
                DB::table('pricing_operational_fees')->updateOrInsert(
                    [
                        'currency' => strtoupper((string) $feeRow['currency']),
                        'payment_method' => strtolower((string) $feeRow['payment_method']),
                    ],
                    [
                        'fee_percent' => round((float) $feeRow['fee_percent'], 2),
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        if (Schema::hasTable('pricing_platform_commissions')) {
            $commissionRows = [];

            foreach ($rows as $row) {
                $currency = strtoupper((string) $row['currency']);
                $metadata = json_decode((string) ($row['metadata'] ?? ''), true);
                $metadata = is_array($metadata) ? $metadata : [];
                $planCode = strtolower((string) ($metadata['plan_code'] ?? ''));

                if ($planCode !== '') {
                    $methods = $currency === 'BRL' ? ['card', 'pix'] : ['card'];
                    foreach ($methods as $method) {
                        $commissionRows[] = [
                            'plan_code' => $planCode,
                            'currency' => $currency,
                            'payment_method' => $method,
                            'commission_percent' => round((float) ($row['platform_fee_percent'] ?? 0), 2),
                        ];
                    }
                }

                if (
                    (string) ($row['account_mode'] ?? '') === 'scheduling_with_payments'
                    && isset($metadata['premium_fee_percent'])
                ) {
                    $methods = $currency === 'BRL' ? ['card', 'pix'] : ['card'];
                    foreach ($methods as $method) {
                        $commissionRows[] = [
                            'plan_code' => 'payments_premium',
                            'currency' => $currency,
                            'payment_method' => $method,
                            'commission_percent' => round((float) $metadata['premium_fee_percent'], 2),
                        ];
                    }
                }
            }

            foreach ($commissionRows as $commissionRow) {
                DB::table('pricing_platform_commissions')->updateOrInsert(
                    [
                        'plan_code' => strtolower((string) $commissionRow['plan_code']),
                        'currency' => strtoupper((string) $commissionRow['currency']),
                        'payment_method' => strtolower((string) $commissionRow['payment_method']),
                    ],
                    [
                        'commission_percent' => round((float) ($commissionRow['commission_percent'] ?? 0), 2),
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }
}
