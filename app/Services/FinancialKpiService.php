<?php

namespace App\Services;

use App\Models\BillingTransaction;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinancialKpiService
{
    /**
     * Build the PR-06 financial KPI snapshot for internal dashboards.
     *
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $fxRates = $this->resolveFxRates();
        $missingCurrencies = [];

        $revenueRows = BillingTransaction::query()
            ->selectRaw('UPPER(currency) as currency, SUM(amount) as total_amount, COUNT(*) as transactions')
            ->where('status', 'paid')
            ->groupBy(DB::raw('UPPER(currency)'))
            ->orderBy('currency')
            ->get();

        $revenueByCurrency = [];
        $revenueConvertedBrl = 0.0;

        foreach ($revenueRows as $row) {
            $currency = strtoupper((string) ($row->currency ?? 'BRL'));
            $amount = round((float) ($row->total_amount ?? 0), 2);
            $amountBrl = $this->convertToBrl($amount, $currency, $fxRates, $missingCurrencies);

            $revenueByCurrency[] = [
                'currency' => $currency,
                'amount' => $amount,
                'amount_brl' => $amountBrl,
                'transactions' => (int) ($row->transactions ?? 0),
                'fx_rate_to_brl' => (float) ($fxRates[$currency] ?? 0),
            ];

            $revenueConvertedBrl += $amountBrl;
        }

        $countryRows = BillingTransaction::query()
            ->join('users', 'users.id', '=', 'billing_transactions.user_id')
            ->selectRaw("COALESCE(NULLIF(UPPER(users.country_code), ''), 'N/A') as country_code")
            ->selectRaw('UPPER(billing_transactions.currency) as currency')
            ->selectRaw('SUM(billing_transactions.amount) as total_amount')
            ->selectRaw('COUNT(*) as transactions')
            ->where('billing_transactions.status', 'paid')
            ->groupBy(
                DB::raw("COALESCE(NULLIF(UPPER(users.country_code), ''), 'N/A')"),
                DB::raw('UPPER(billing_transactions.currency)')
            )
            ->orderBy('country_code')
            ->get();

        $countryBuckets = [];

        foreach ($countryRows as $row) {
            $countryCode = (string) ($row->country_code ?? 'N/A');
            $currency = strtoupper((string) ($row->currency ?? 'BRL'));
            $amount = round((float) ($row->total_amount ?? 0), 2);
            $amountBrl = $this->convertToBrl($amount, $currency, $fxRates, $missingCurrencies);
            $transactions = (int) ($row->transactions ?? 0);

            if (!isset($countryBuckets[$countryCode])) {
                $countryBuckets[$countryCode] = [
                    'country_code' => $countryCode,
                    'transactions' => 0,
                    'total_brl' => 0.0,
                    'currencies' => [],
                ];
            }

            $countryBuckets[$countryCode]['transactions'] += $transactions;
            $countryBuckets[$countryCode]['total_brl'] = round(
                $countryBuckets[$countryCode]['total_brl'] + $amountBrl,
                2
            );
            $countryBuckets[$countryCode]['currencies'][] = [
                'currency' => $currency,
                'amount' => $amount,
                'amount_brl' => $amountBrl,
                'fx_rate_to_brl' => (float) ($fxRates[$currency] ?? 0),
            ];
        }

        $gmvByCountry = collect($countryBuckets)
            ->values()
            ->sortByDesc('total_brl')
            ->values()
            ->all();

        $totalBookings = Booking::query()->count();
        $paidBookings = Booking::query()
            ->where(function ($query) {
                $query->where('is_paid', true)
                    ->orWhere('amount_paid', '>', 0);
            })
            ->count();

        $paidAppointmentsRate = $totalBookings > 0
            ? round(($paidBookings / $totalBookings) * 100, 2)
            : 0.0;

        $clientsQuery = User::query()->where('is_super_admin', false);
        $totalClients = (clone $clientsQuery)->count();
        $paymentsModeClients = (clone $clientsQuery)
            ->where('account_mode', 'scheduling_with_payments')
            ->count();

        $modeUpgradeRate = $totalClients > 0
            ? round(($paymentsModeClients / $totalClients) * 100, 2)
            : 0.0;

        return [
            'revenue_by_currency' => $revenueByCurrency,
            'revenue_converted_brl' => round($revenueConvertedBrl, 2),
            'gmv_by_country' => $gmvByCountry,
            'paid_appointments' => [
                'paid' => $paidBookings,
                'total' => $totalBookings,
                'rate' => $paidAppointmentsRate,
            ],
            'mode_upgrade' => [
                'payments_mode_accounts' => $paymentsModeClients,
                'total_accounts' => $totalClients,
                'rate' => $modeUpgradeRate,
            ],
            'fx_rates_to_brl' => $fxRates,
            'fx_missing_currencies' => array_values(array_keys($missingCurrencies)),
        ];
    }

    /**
     * @return array<string, float>
     */
    private function resolveFxRates(): array
    {
        $configuredRates = (array) config('analytics.currency_to_brl', []);
        $fxRates = [];

        foreach ($configuredRates as $currency => $rate) {
            $normalizedCurrency = strtoupper((string) $currency);
            $normalizedRate = (float) $rate;

            if ($normalizedCurrency === '' || $normalizedRate <= 0) {
                continue;
            }

            $fxRates[$normalizedCurrency] = $normalizedRate;
        }

        if (!isset($fxRates['BRL'])) {
            $fxRates['BRL'] = 1.0;
        }

        return $fxRates;
    }

    /**
     * @param  array<string, float>  $fxRates
     * @param  array<string, bool>  $missingCurrencies
     */
    private function convertToBrl(
        float $amount,
        string $currency,
        array $fxRates,
        array &$missingCurrencies
    ): float {
        if (!isset($fxRates[$currency])) {
            $missingCurrencies[$currency] = true;
            return 0.0;
        }

        return round($amount * $fxRates[$currency], 2);
    }
}
