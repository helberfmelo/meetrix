<?php

namespace App\Services;

use App\Models\PricingOperationalFee;
use App\Models\PricingPlatformCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PricingFeeEngineService
{
    private const DEFAULT_PAYMENT_METHODS_BY_CURRENCY = [
        'BRL' => ['card', 'pix'],
        'USD' => ['card'],
        'EUR' => ['card'],
    ];

    private const DEFAULT_PAYMENT_METHOD_LABELS = [
        'card' => 'Cartao',
        'pix' => 'PIX',
    ];

    public function __construct(private readonly GeoPricingCatalogService $geoPricingCatalogService)
    {
    }

    /**
     * @return array<int, string>
     */
    public function supportedCurrencies(): array
    {
        return $this->geoPricingCatalogService->supportedCurrencies();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function supportedPaymentMethodsByCurrency(): array
    {
        $supportedCurrencies = $this->supportedCurrencies();
        $methods = [];

        foreach ($supportedCurrencies as $currency) {
            $upperCurrency = strtoupper((string) $currency);
            $methods[$upperCurrency] = self::DEFAULT_PAYMENT_METHODS_BY_CURRENCY[$upperCurrency] ?? ['card'];
        }

        return $methods;
    }

    /**
     * @return array<int, string>
     */
    public function supportedPaymentMethods(): array
    {
        $methods = [];

        foreach ($this->supportedPaymentMethodsByCurrency() as $currencyMethods) {
            foreach ($currencyMethods as $method) {
                $methods[$method] = $method;
            }
        }

        return array_values($methods);
    }

    /**
     * @return array<string, string>
     */
    public function paymentMethodLabels(): array
    {
        $labels = [];
        foreach ($this->supportedPaymentMethods() as $method) {
            $labels[$method] = self::DEFAULT_PAYMENT_METHOD_LABELS[$method] ?? strtoupper($method);
        }

        return $labels;
    }

    /**
     * @return array<int, string>
     */
    public function supportedPlanCodes(): array
    {
        $codes = [
            'schedule_starter',
            'payments_pro',
            'payments_premium',
        ];

        if (!Schema::hasTable('geo_pricing')) {
            return array_values(array_unique($codes));
        }

        $rows = DB::table('geo_pricing')
            ->select(['account_mode', 'metadata'])
            ->get();

        foreach ($rows as $row) {
            $metadata = is_array($row->metadata)
                ? $row->metadata
                : json_decode((string) $row->metadata, true);
            $metadata = is_array($metadata) ? $metadata : [];

            if (!empty($metadata['plan_code'])) {
                $codes[] = (string) $metadata['plan_code'];
            }

            if (
                (string) $row->account_mode === 'scheduling_with_payments'
                && array_key_exists('premium_price', $metadata)
                && $metadata['premium_price'] !== null
            ) {
                $codes[] = 'payments_premium';
            }
        }

        return array_values(array_unique(array_filter($codes, fn ($code) => trim((string) $code) !== '')));
    }

    /**
     * @return array<string, mixed>
     */
    public function calculate(
        string $planCode,
        string $currency,
        string $paymentMethod,
        ?float $grossAmount = null
    ): array {
        $normalizedPlanCode = trim(strtolower($planCode));
        $normalizedCurrency = strtoupper(trim($currency));
        $normalizedPaymentMethod = trim(strtolower($paymentMethod));

        $commission = Schema::hasTable('pricing_platform_commissions')
            ? PricingPlatformCommission::query()
                ->where('plan_code', $normalizedPlanCode)
                ->where('currency', $normalizedCurrency)
                ->where('payment_method', $normalizedPaymentMethod)
                ->first()
            : null;

        $operationalFee = Schema::hasTable('pricing_operational_fees')
            ? PricingOperationalFee::query()
                ->where('currency', $normalizedCurrency)
                ->where('payment_method', $normalizedPaymentMethod)
                ->first()
            : null;

        $commissionPercent = round((float) ($commission?->commission_percent ?? 0), 2);
        $operationalFeePercent = round((float) ($operationalFee?->fee_percent ?? 0), 2);
        $totalFeePercent = round($commissionPercent + $operationalFeePercent, 2);

        $commissionActive = (bool) ($commission?->is_active ?? false);
        $operationalFeeActive = (bool) ($operationalFee?->is_active ?? false);

        $effectiveCommissionPercent = $commissionActive ? $commissionPercent : 0.0;
        $effectiveOperationalFeePercent = $operationalFeeActive ? $operationalFeePercent : 0.0;
        $effectiveTotalFeePercent = round($effectiveCommissionPercent + $effectiveOperationalFeePercent, 2);

        $resolvedGrossAmount = $grossAmount !== null ? round(max(0, $grossAmount), 2) : null;
        $totalFeeAmount = $resolvedGrossAmount !== null
            ? round($resolvedGrossAmount * ($effectiveTotalFeePercent / 100), 2)
            : null;
        $netAmount = $resolvedGrossAmount !== null
            ? round(max(0, $resolvedGrossAmount - ($totalFeeAmount ?? 0)), 2)
            : null;

        return [
            'plan_code' => $normalizedPlanCode,
            'currency' => $normalizedCurrency,
            'payment_method' => $normalizedPaymentMethod,
            'commission_percent' => $commissionPercent,
            'operational_fee_percent' => $operationalFeePercent,
            'total_fee_percent' => $totalFeePercent,
            'effective_commission_percent' => $effectiveCommissionPercent,
            'effective_operational_fee_percent' => $effectiveOperationalFeePercent,
            'effective_total_fee_percent' => $effectiveTotalFeePercent,
            'commission_active' => $commissionActive,
            'operational_fee_active' => $operationalFeeActive,
            'gross_amount' => $resolvedGrossAmount,
            'total_fee_amount' => $totalFeeAmount,
            'net_amount' => $netAmount,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function commissionsWithComposition(): array
    {
        if (!Schema::hasTable('pricing_platform_commissions')) {
            return [];
        }

        return PricingPlatformCommission::query()
            ->orderBy('currency')
            ->orderBy('plan_code')
            ->orderBy('payment_method')
            ->get()
            ->map(function (PricingPlatformCommission $commission) {
                $composition = $this->calculate(
                    (string) $commission->plan_code,
                    (string) $commission->currency,
                    (string) $commission->payment_method
                );

                return [
                    'id' => $commission->id,
                    'plan_code' => strtolower((string) $commission->plan_code),
                    'currency' => strtoupper((string) $commission->currency),
                    'payment_method' => strtolower((string) $commission->payment_method),
                    'commission_percent' => (float) $commission->commission_percent,
                    'is_active' => (bool) $commission->is_active,
                    'composition' => $composition,
                    'updated_at' => optional($commission->updated_at)?->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }
}
