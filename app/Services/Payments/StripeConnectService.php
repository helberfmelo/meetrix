<?php

namespace App\Services\Payments;

use App\Models\ConnectedAccount;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeConnectService
{
    /**
     * @throws ApiErrorException
     */
    public function createAccountLink(User $user): array
    {
        $connectedAccount = $this->findOrCreateConnectedAccount($user);
        $this->syncConnectedAccount($connectedAccount);

        $refreshUrl = (string) (config('payments.stripe.connect_refresh_url') ?: rtrim((string) config('app.url'), '/') . '/dashboard/account?connect=refresh');
        $returnUrl = (string) (config('payments.stripe.connect_return_url') ?: rtrim((string) config('app.url'), '/') . '/dashboard/account?connect=return');

        $accountLink = $this->stripe()->accountLinks->create([
            'account' => $connectedAccount->provider_account_id,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);

        return [
            'url' => $accountLink->url,
            'expires_at' => $accountLink->expires_at,
            'connected_account' => $connectedAccount->fresh(),
        ];
    }

    /**
     * @throws ApiErrorException
     */
    public function findOrCreateConnectedAccount(User $user): ConnectedAccount
    {
        $connectedAccount = ConnectedAccount::query()
            ->where('user_id', $user->id)
            ->where('provider', 'stripe_connect')
            ->latest()
            ->first();

        if ($connectedAccount && $connectedAccount->provider_account_id) {
            return $connectedAccount;
        }

        $stripeAccount = $this->stripe()->accounts->create([
            'type' => 'express',
            'country' => strtoupper((string) ($user->country_code ?: 'BR')),
            'email' => $user->email,
            'metadata' => [
                'user_id' => (string) $user->id,
                'account_mode' => (string) ($user->account_mode ?? 'scheduling_with_payments'),
            ],
        ]);

        if (!$connectedAccount) {
            $connectedAccount = new ConnectedAccount([
                'user_id' => $user->id,
                'provider' => 'stripe_connect',
            ]);
        }

        $connectedAccount->fill([
            'provider_account_id' => $stripeAccount->id,
            'status' => 'pending',
            'charges_enabled' => (bool) $stripeAccount->charges_enabled,
            'payouts_enabled' => (bool) $stripeAccount->payouts_enabled,
            'details_submitted' => (bool) $stripeAccount->details_submitted,
            'capabilities' => (array) $stripeAccount->capabilities,
            'metadata' => (array) $stripeAccount->metadata,
        ]);
        $connectedAccount->save();

        return $connectedAccount;
    }

    /**
     * @throws ApiErrorException
     */
    public function syncConnectedAccount(ConnectedAccount $connectedAccount): ConnectedAccount
    {
        if (!$connectedAccount->provider_account_id) {
            return $connectedAccount;
        }

        $account = $this->stripe()->accounts->retrieve($connectedAccount->provider_account_id, []);

        $connectedAccount->update([
            'status' => ((bool) $account->charges_enabled && (bool) $account->details_submitted) ? 'active' : 'pending',
            'charges_enabled' => (bool) $account->charges_enabled,
            'payouts_enabled' => (bool) $account->payouts_enabled,
            'details_submitted' => (bool) $account->details_submitted,
            'capabilities' => (array) $account->capabilities,
            'metadata' => (array) $account->metadata,
        ]);

        return $connectedAccount->fresh();
    }

    public function getActiveConnectedAccount(User $user): ?ConnectedAccount
    {
        return ConnectedAccount::query()
            ->where('user_id', $user->id)
            ->where('provider', 'stripe_connect')
            ->where('status', 'active')
            ->where('charges_enabled', true)
            ->whereNotNull('provider_account_id')
            ->latest()
            ->first();
    }

    /**
     * Build split payload for Stripe Checkout Session payment_intent_data.
     *
     * @return array<string, mixed>|null
     */
    public function resolveSplitPayload(User $user, int $amountInCents, string $currency): ?array
    {
        $connectedAccount = $this->resolveUsableConnectedAccount($user);
        if (!$connectedAccount || !$connectedAccount->provider_account_id) {
            return null;
        }

        $platformFeePercent = max(0, (float) ($user->platform_fee_percent ?? 0));
        $platformFeeAmount = (int) round($amountInCents * ($platformFeePercent / 100), 0, PHP_ROUND_HALF_UP);
        $netAmount = max(0, $amountInCents - $platformFeeAmount);

        $paymentIntentData = [
            'transfer_data' => [
                'destination' => $connectedAccount->provider_account_id,
            ],
            'metadata' => [
                'connected_account_id' => (string) $connectedAccount->id,
                'connected_account_ref' => (string) $connectedAccount->provider_account_id,
                'platform_fee_percent' => (string) $platformFeePercent,
                'currency' => strtolower($currency),
                'net_amount_cents' => (string) $netAmount,
            ],
        ];

        if ($platformFeeAmount > 0) {
            $paymentIntentData['application_fee_amount'] = $platformFeeAmount;
        }

        return [
            'payment_intent_data' => $paymentIntentData,
            'connected_account' => $connectedAccount,
            'platform_fee_percent' => $platformFeePercent,
            'platform_fee_amount_cents' => $platformFeeAmount,
            'net_amount_cents' => $netAmount,
        ];
    }

    private function resolveUsableConnectedAccount(User $user): ?ConnectedAccount
    {
        $connectedAccount = $this->getActiveConnectedAccount($user);
        if (!$connectedAccount || !$connectedAccount->provider_account_id) {
            return null;
        }

        try {
            $syncedAccount = $this->syncConnectedAccount($connectedAccount);

            if (
                $syncedAccount->status !== 'active'
                || !$syncedAccount->charges_enabled
                || !$syncedAccount->provider_account_id
            ) {
                return null;
            }

            return $syncedAccount;
        } catch (ApiErrorException $exception) {
            $this->markConnectedAccountUnavailable($connectedAccount, $exception->getMessage());

            Log::warning('Stripe Connect split fallback applied: connected account unavailable.', [
                'user_id' => $user->id,
                'connected_account_id' => $connectedAccount->id,
                'provider_account_id' => $connectedAccount->provider_account_id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function markConnectedAccountUnavailable(ConnectedAccount $connectedAccount, string $reason): void
    {
        $metadata = is_array($connectedAccount->metadata) ? $connectedAccount->metadata : [];
        $metadata['split_fallback_last_error'] = $reason;
        $metadata['split_fallback_last_at'] = now()->toIso8601String();

        $connectedAccount->update([
            'status' => 'pending',
            'charges_enabled' => false,
            'metadata' => $metadata,
        ]);
    }

    private function stripe(): StripeClient
    {
        $secret = (string) (config('payments.stripe.secret') ?: config('services.stripe.secret'));
        if ($secret === '') {
            throw new RuntimeException('STRIPE secret is not configured.');
        }

        return new StripeClient($secret);
    }
}
