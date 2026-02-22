<?php

namespace App\Services\Payments;

use App\Models\User;

class CheckoutPaymentMethodService
{
    /**
     * Resolve Stripe payment_method_types for a booking checkout.
     *
     * @return array<int, string>
     */
    public function resolveForBooking(
        ?User $merchant,
        string $currency,
        bool $splitInUse = false,
        bool $manualCapture = false
    ): array {
        $currencyCode = strtoupper(trim($currency));
        $methods = $this->methodsFromCurrencyMap($currencyCode);

        if ($manualCapture) {
            // Pix does not support manual capture workflows.
            $methods = array_values(array_filter($methods, fn (string $method) => $method !== 'pix'));
        }

        if (in_array('pix', $methods, true)) {
            if (!$this->isPixEnabled($merchant, $splitInUse)) {
                $methods = array_values(array_filter($methods, fn (string $method) => $method !== 'pix'));
            }
        }

        return $methods === [] ? ['card'] : $methods;
    }

    /**
     * Resolve Stripe payment_method_types for Checkout in subscription mode.
     *
     * @return array<int, string>
     */
    public function resolveForSubscriptionCheckout(string $currency): array
    {
        $currencyCode = strtoupper(trim($currency));
        $methods = $this->methodsFromCurrencyMap($currencyCode);

        // Stripe Pix docs: not supported in Checkout when mode=subscription.
        $methods = array_values(array_filter($methods, fn (string $method) => $method !== 'pix'));

        return $methods === [] ? ['card'] : $methods;
    }

    /**
     * Build a public/UI-ready catalog for checkout payment methods.
     *
     * @return array<string, mixed>
     */
    public function buildCatalog(
        ?string $currency,
        ?User $merchant = null,
        string $context = 'booking'
    ): array
    {
        $currencyCode = strtoupper(trim((string) $currency));
        if ($currencyCode === '') {
            $currencyCode = strtoupper((string) ($merchant?->currency ?? 'BRL'));
        }

        $normalizedContext = strtolower(trim($context));
        if ($normalizedContext === 'subscription') {
            $methods = $this->resolveForSubscriptionCheckout($currencyCode);
        } else {
            $methods = $this->resolveForBooking(
                $merchant,
                $currencyCode,
                $this->shouldAssumeSplitInUse($merchant)
            );
            $normalizedContext = 'booking';
        }

        $labels = [
            'card' => 'Cartao',
            'pix' => 'PIX',
        ];

        $uiMethods = [];
        foreach ($methods as $method) {
            if ($method === 'card') {
                $uiMethods[] = [
                    'code' => 'card',
                    'label' => $labels['card'],
                    'icon' => 'credit_card',
                    'brand_icons' => ['visa', 'mastercard', 'amex'],
                    'monochrome' => true,
                ];
                continue;
            }

            if ($method === 'pix') {
                $uiMethods[] = [
                    'code' => 'pix',
                    'label' => $labels['pix'],
                    'icon' => 'pix',
                    'brand_icons' => [],
                    'monochrome' => true,
                ];
            }
        }

        return [
            'context' => $normalizedContext,
            'currency' => $currencyCode,
            'stripe_payment_method_types' => $methods,
            'methods' => $uiMethods,
            'flags' => [
                'pix_enabled' => in_array('pix', $methods, true),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function methodsFromCurrencyMap(string $currency): array
    {
        $map = (array) config('payments.checkout.payment_methods_by_currency', []);
        $rawMethods = $map[$currency] ?? ['card'];

        $known = [];
        foreach ((array) $rawMethods as $method) {
            $normalized = strtolower(trim((string) $method));
            if (!in_array($normalized, ['card', 'pix'], true)) {
                continue;
            }

            $known[$normalized] = $normalized;
        }

        $methods = array_values($known);

        return $methods === [] ? ['card'] : $methods;
    }

    private function isPixEnabled(?User $merchant, bool $splitInUse): bool
    {
        if ($this->isProductionEnvironment() && (bool) config('payments.checkout.pix.production_lock', true)) {
            return false;
        }

        if (!filter_var(config('payments.checkout.pix.enabled', false), FILTER_VALIDATE_BOOL)) {
            return false;
        }

        if (
            filter_var(config('payments.checkout.pix.requires_payments_mode', true), FILTER_VALIDATE_BOOL)
            && ($merchant?->account_mode ?? 'scheduling_only') !== 'scheduling_with_payments'
        ) {
            return false;
        }

        if (
            $splitInUse
            && !filter_var(config('payments.checkout.pix.split_enabled', false), FILTER_VALIDATE_BOOL)
        ) {
            return false;
        }

        return true;
    }

    private function isProductionEnvironment(): bool
    {
        if (app()->environment('production')) {
            return true;
        }

        return strtolower((string) config('app.env', '')) === 'production';
    }

    private function shouldAssumeSplitInUse(?User $merchant): bool
    {
        if (!$merchant) {
            return false;
        }

        return $merchant->connectedAccounts()
            ->where('provider', 'stripe_connect')
            ->where('status', 'active')
            ->where('charges_enabled', true)
            ->whereNotNull('provider_account_id')
            ->exists();
    }
}
