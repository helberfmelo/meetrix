<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class GeoPricingCatalogService
{
    /**
     * Resolve regional pricing bucket from country/locale fallbacks.
     */
    public function resolveRegion(?string $countryCode = null, ?string $locale = null): string
    {
        $country = strtoupper((string) $countryCode);
        if (in_array($country, ['BR'], true)) {
            return 'BR';
        }

        if (in_array($country, ['US', 'CA', 'AU'], true)) {
            return 'USD';
        }

        if ($country !== '') {
            return 'EUR';
        }

        $normalizedLocale = strtolower(str_replace('_', '-', (string) $locale));
        if ($normalizedLocale === 'pt-br') {
            return 'BR';
        }

        if (str_starts_with($normalizedLocale, 'en')) {
            return 'USD';
        }

        return 'EUR';
    }

    /**
     * Return pricing catalog for a region.
     *
     * @return array{
     *     region:string,
     *     currency:string,
     *     plans:array<string,array<string,mixed>>
     * }
     */
    public function getCatalogForRegion(string $region): array
    {
        $regionCode = strtoupper($region);

        $rows = DB::table('geo_pricing')
            ->where('region_code', $regionCode)
            ->where('is_active', true)
            ->orderBy('account_mode')
            ->get();

        if ($rows->isEmpty()) {
            $rows = DB::table('geo_pricing')
                ->where('region_code', 'EUR')
                ->where('is_active', true)
                ->orderBy('account_mode')
                ->get();

            $regionCode = 'EUR';
        }

        $plans = [];
        $currency = 'EUR';

        foreach ($rows as $row) {
            $currency = $row->currency;
            $metadata = $row->metadata ? json_decode($row->metadata, true) : [];

            $plans[$row->account_mode] = [
                'monthly_price' => (float) $row->monthly_price,
                'annual_price' => (float) $row->annual_price,
                'platform_fee_percent' => (float) $row->platform_fee_percent,
                'plan_code' => $metadata['plan_code'] ?? null,
                'label' => $metadata['label'] ?? null,
                'premium_price' => isset($metadata['premium_price']) ? (float) $metadata['premium_price'] : null,
                'premium_fee_percent' => isset($metadata['premium_fee_percent']) ? (float) $metadata['premium_fee_percent'] : null,
            ];
        }

        return [
            'region' => $regionCode,
            'currency' => $currency,
            'plans' => $plans,
        ];
    }
}
