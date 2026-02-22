<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GeoPricingCatalogService
{
    /**
     * Default country grouping to billing region.
     */
    private const COUNTRY_TO_REGION = [
        'BR' => 'BR',
        'US' => 'USD',
        'CA' => 'USD',
        'AU' => 'USD',
    ];

    /**
     * Supported billing region -> currency pairs.
     */
    private const REGION_TO_CURRENCY = [
        'BR' => 'BRL',
        'USD' => 'USD',
        'EUR' => 'EUR',
    ];

    /**
     * Built-in locale defaults (used if table is empty or unavailable).
     */
    private const DEFAULT_LOCALE_CURRENCY_MAP = [
        ['locale_code' => 'pt-br', 'region_code' => 'BR', 'currency' => 'BRL', 'is_active' => true],
        ['locale_code' => 'en', 'region_code' => 'USD', 'currency' => 'USD', 'is_active' => true],
        ['locale_code' => 'en-us', 'region_code' => 'USD', 'currency' => 'USD', 'is_active' => true],
        ['locale_code' => 'es', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'fr', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'de', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'pt', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'zh', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'zh-cn', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'ja', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'ko', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'it', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'ru', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'tr', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
        ['locale_code' => 'ar', 'region_code' => 'EUR', 'currency' => 'EUR', 'is_active' => true],
    ];

    /**
     * Resolve regional pricing bucket from country/geoip/locale fallbacks.
     */
    public function resolveRegion(?string $countryCode = null, ?string $locale = null, ?string $ipAddress = null): string
    {
        return $this->resolvePricingContext($countryCode, $locale, $ipAddress)['region'];
    }

    /**
     * Resolve pricing context and explain where the decision came from.
     *
     * @return array{
     *     region:string,
     *     currency:string,
     *     source:string,
     *     country_code:?string,
     *     detected_country_code:?string,
     *     locale:?string
     * }
     */
    public function resolvePricingContext(
        ?string $countryCode = null,
        ?string $locale = null,
        ?string $ipAddress = null
    ): array
    {
        $normalizedCountry = $this->normalizeCountryCode($countryCode);
        $normalizedLocale = $this->normalizeLocaleCode($locale);
        $detectedCountry = null;
        $region = null;
        $source = 'default';

        if ($normalizedCountry !== null) {
            $region = $this->regionFromCountry($normalizedCountry);
            $source = 'country_code';
        }

        if ($region === null) {
            $localeResolution = $this->resolveRegionByLocale($normalizedLocale);

            if ($localeResolution !== null) {
                $region = $localeResolution['region'];
                $source = $localeResolution['source'];
            }
        }

        if ($region === null && config('pricing.geoip_enabled') && !empty($ipAddress)) {
            $detectedCountry = $this->detectCountryCodeFromIp($ipAddress);

            if ($detectedCountry !== null) {
                $region = $this->regionFromCountry($detectedCountry);
                $source = 'geoip';
            }
        }

        $resolvedRegion = $region ?? 'EUR';

        return [
            'region' => $resolvedRegion,
            'currency' => $this->currencyForRegion($resolvedRegion),
            'source' => $source,
            'country_code' => $normalizedCountry,
            'detected_country_code' => $detectedCountry,
            'locale' => $normalizedLocale,
        ];
    }

    /**
     * Return supported pricing region codes.
     *
     * @return array<int, string>
     */
    public function supportedRegions(): array
    {
        return array_keys(self::REGION_TO_CURRENCY);
    }

    /**
     * Return supported currencies for pricing.
     *
     * @return array<int, string>
     */
    public function supportedCurrencies(): array
    {
        return array_values(self::REGION_TO_CURRENCY);
    }

    /**
     * Return supported account modes controlled by pricing matrix.
     *
     * @return array<int, string>
     */
    public function supportedAccountModes(): array
    {
        return ['scheduling_only', 'scheduling_with_payments'];
    }

    /**
     * Resolve expected currency for a billing region.
     */
    public function currencyForRegion(string $region): string
    {
        $regionCode = strtoupper(trim($region));
        return self::REGION_TO_CURRENCY[$regionCode] ?? 'EUR';
    }

    /**
     * Resolve billing region from currency.
     */
    public function regionForCurrency(string $currency): string
    {
        $upperCurrency = strtoupper(trim($currency));

        return match ($upperCurrency) {
            'BRL' => 'BR',
            'USD' => 'USD',
            default => 'EUR',
        };
    }

    /**
     * Normalize locale value to lowercase IETF-style tag.
     */
    public function normalizeLocaleCode(?string $locale): ?string
    {
        $rawLocale = strtolower(trim((string) $locale));
        if ($rawLocale === '') {
            return null;
        }

        $normalized = str_replace('_', '-', $rawLocale);
        return preg_replace('/[^a-z0-9-]/', '', $normalized) ?: null;
    }

    /**
     * Return locale-to-currency mappings used by pricing resolver/admin.
     *
     * @return array<int, array{locale_code:string, region_code:string, currency:string, is_active:bool}>
     */
    public function getLocaleCurrencyMappings(): array
    {
        if (!Schema::hasTable('pricing_locale_currency_maps')) {
            return self::DEFAULT_LOCALE_CURRENCY_MAP;
        }

        $rows = DB::table('pricing_locale_currency_maps')
            ->orderBy('locale_code')
            ->get();

        if ($rows->isEmpty()) {
            return self::DEFAULT_LOCALE_CURRENCY_MAP;
        }

        return $rows->map(function ($row) {
            $region = strtoupper((string) ($row->region_code ?? $this->regionForCurrency((string) $row->currency)));
            $currency = strtoupper((string) ($row->currency ?? $this->currencyForRegion($region)));

            return [
                'locale_code' => $this->normalizeLocaleCode((string) $row->locale_code) ?? 'en',
                'region_code' => $region,
                'currency' => $currency,
                'is_active' => (bool) $row->is_active,
            ];
        })->all();
    }

    /**
     * Return geo pricing matrix grouped by region for admin editing.
     *
     * @return array<int, array{
     *     region_code:string,
     *     currency:string,
     *     plans:array<string, array{
     *         account_mode:string,
     *         monthly_price:float,
     *         annual_price:float,
     *         platform_fee_percent:float,
     *         premium_price:?float,
     *         premium_fee_percent:?float,
     *         label:?string,
     *         plan_code:?string,
     *         is_active:bool
     *     }>
     * }>
     */
    public function getGeoPricingMatrix(): array
    {
        $rows = DB::table('geo_pricing')
            ->orderBy('region_code')
            ->orderBy('account_mode')
            ->get();

        $matrix = [];

        foreach ($this->supportedRegions() as $regionCode) {
            $matrix[$regionCode] = [
                'region_code' => $regionCode,
                'currency' => $this->currencyForRegion($regionCode),
                'plans' => [],
            ];

            foreach ($this->supportedAccountModes() as $mode) {
                $matrix[$regionCode]['plans'][$mode] = [
                    'account_mode' => $mode,
                    'monthly_price' => 0.0,
                    'annual_price' => 0.0,
                    'platform_fee_percent' => 0.0,
                    'premium_price' => null,
                    'premium_fee_percent' => null,
                    'label' => null,
                    'plan_code' => null,
                    'is_active' => false,
                ];
            }
        }

        foreach ($rows as $row) {
            $regionCode = strtoupper((string) $row->region_code);
            $mode = (string) $row->account_mode;

            if (!isset($matrix[$regionCode]) || !isset($matrix[$regionCode]['plans'][$mode])) {
                continue;
            }

            $metadata = is_array($row->metadata) ? $row->metadata : json_decode((string) $row->metadata, true);
            $metadata = is_array($metadata) ? $metadata : [];

            $matrix[$regionCode]['currency'] = strtoupper((string) $row->currency);
            $matrix[$regionCode]['plans'][$mode] = [
                'account_mode' => $mode,
                'monthly_price' => (float) $row->monthly_price,
                'annual_price' => (float) $row->annual_price,
                'platform_fee_percent' => (float) $row->platform_fee_percent,
                'premium_price' => isset($metadata['premium_price']) ? (float) $metadata['premium_price'] : null,
                'premium_fee_percent' => isset($metadata['premium_fee_percent']) ? (float) $metadata['premium_fee_percent'] : null,
                'label' => isset($metadata['label']) ? (string) $metadata['label'] : null,
                'plan_code' => isset($metadata['plan_code']) ? (string) $metadata['plan_code'] : null,
                'is_active' => (bool) $row->is_active,
            ];
        }

        return array_values($matrix);
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

    /**
     * Normalize ISO country code.
     */
    private function normalizeCountryCode(?string $countryCode): ?string
    {
        $country = strtoupper(trim((string) $countryCode));
        if ($country === '') {
            return null;
        }

        return preg_match('/^[A-Z]{2}$/', $country) ? $country : null;
    }

    /**
     * Map country to billing region.
     */
    private function regionFromCountry(string $countryCode): string
    {
        return self::COUNTRY_TO_REGION[$countryCode] ?? 'EUR';
    }

    /**
     * Resolve region using locale mapping table first, then hardcoded fallback.
     *
     * @return array{region:string,source:string}|null
     */
    private function resolveRegionByLocale(?string $locale): ?array
    {
        if ($locale === null) {
            return null;
        }

        $candidates = [$locale];
        if (str_contains($locale, '-')) {
            $languageCode = explode('-', $locale)[0];
            if ($languageCode !== '') {
                $candidates[] = $languageCode;
            }
        }

        if (Schema::hasTable('pricing_locale_currency_maps')) {
            $rows = DB::table('pricing_locale_currency_maps')
                ->whereIn('locale_code', $candidates)
                ->where('is_active', true)
                ->get()
                ->keyBy(fn ($row) => $this->normalizeLocaleCode((string) $row->locale_code));

            foreach ($candidates as $index => $candidate) {
                $key = $this->normalizeLocaleCode($candidate);
                if ($key === null || !$rows->has($key)) {
                    continue;
                }

                $row = $rows->get($key);
                $region = strtoupper((string) ($row->region_code ?? $this->regionForCurrency((string) $row->currency)));

                return [
                    'region' => $region ?: 'EUR',
                    'source' => $index === 0 ? 'locale_mapping_exact' : 'locale_mapping_language',
                ];
            }
        }

        if ($locale === 'pt-br') {
            return ['region' => 'BR', 'source' => 'locale_fallback'];
        }

        if (str_starts_with($locale, 'en')) {
            return ['region' => 'USD', 'source' => 'locale_fallback'];
        }

        return ['region' => 'EUR', 'source' => 'locale_fallback'];
    }

    /**
     * Best-effort country detection from request IP.
     */
    private function detectCountryCodeFromIp(string $ipAddress): ?string
    {
        try {
            $geoService = new GeoIPService();
            $country = $geoService->getCountryCode($ipAddress);

            return $this->normalizeCountryCode($country);
        } catch (\Throwable) {
            return null;
        }
    }
}
