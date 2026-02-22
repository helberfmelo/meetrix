<?php

namespace App\Http\Controllers;

use App\Models\ConnectedAccount;
use App\Services\Payments\PaymentFeature;
use App\Services\Payments\StripeConnectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class PaymentConnectController extends Controller
{
    public function __construct(
        private readonly PaymentFeature $paymentFeature,
        private readonly StripeConnectService $stripeConnectService
    ) {
    }

    public function accountLink(Request $request)
    {
        $user = $request->user();

        $eligibilityError = $this->resolveEligibilityError($request);
        if ($eligibilityError) {
            return $eligibilityError;
        }

        try {
            $result = $this->stripeConnectService->createAccountLink($user);

            return response()->json([
                'status' => 'ok',
                'account_link_url' => $result['url'],
                'expires_at' => $result['expires_at'],
                'connected_account' => $result['connected_account'],
            ]);
        } catch (ApiErrorException|\RuntimeException $exception) {
            Log::warning('Stripe Connect account link failed.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Falha ao preparar onboarding financeiro.',
                'error_code' => 'connect_account_link_failed',
            ], 422);
        }
    }

    public function embeddedSession(Request $request): JsonResponse
    {
        $user = $request->user();

        $eligibilityError = $this->resolveEligibilityError($request);
        if ($eligibilityError) {
            return $eligibilityError;
        }

        try {
            $result = $this->stripeConnectService->createEmbeddedOnboardingSession($user);
            $connectedAccount = $result['connected_account'] ?? null;
            $receivingReady = $this->stripeConnectService->isReceivingReady($connectedAccount);

            return response()->json([
                'status' => 'ok',
                'client_secret' => $result['client_secret'],
                'expires_at' => $result['expires_at'],
                'onboarding_required' => !$receivingReady,
                'receiving_ready' => $receivingReady,
                'connected_account_summary' => $this->buildConnectedAccountSummary($connectedAccount),
            ]);
        } catch (ApiErrorException|\RuntimeException $exception) {
            Log::warning('Stripe Connect embedded session failed.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Falha ao preparar onboarding financeiro embutido.',
                'error_code' => 'connect_embedded_session_failed',
            ], 422);
        }
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $paymentsEnabledForUser = $this->paymentFeature->isEnabledForUser($user);
        $connectedAccount = $this->stripeConnectService->syncConnectedAccountSafely(
            $this->stripeConnectService->getLatestConnectedAccount($user),
            [
                'user_id' => $user->id,
                'source' => 'payments_connect_status',
            ]
        );

        $receivingReady = $this->stripeConnectService->isReceivingReady($connectedAccount);
        $onboardingRequired = ($user->account_mode ?? 'scheduling_only') === 'scheduling_with_payments'
            && $paymentsEnabledForUser
            && !$receivingReady;

        $publishableKey = (string) (config('payments.stripe.key') ?: config('services.stripe.key'));

        return response()->json([
            'payments_enabled_for_user' => $paymentsEnabledForUser,
            'payments_enabled_global' => $this->paymentFeature->isGloballyEnabled(),
            'account_mode' => $user->account_mode ?? 'scheduling_only',
            'onboarding_required' => $onboardingRequired,
            'receiving_ready' => $receivingReady,
            'connected_account_summary' => $this->buildConnectedAccountSummary($connectedAccount),
            'connect_publishable_key' => $publishableKey !== '' ? $publishableKey : null,
            'connected_account' => $connectedAccount,
        ]);
    }

    private function resolveEligibilityError(Request $request): ?JsonResponse
    {
        $user = $request->user();

        if (($user->account_mode ?? 'scheduling_only') !== 'scheduling_with_payments') {
            return response()->json([
                'message' => 'Conta em modo agenda. Upgrade para habilitar pagamentos.',
                'error_code' => 'account_mode_not_supported',
            ], 422);
        }

        if (!$this->paymentFeature->isEnabledForUser($user)) {
            return response()->json([
                'message' => 'Pagamentos ainda nao habilitados para esta conta.',
                'error_code' => 'payments_feature_disabled',
            ], 409);
        }

        return null;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function buildConnectedAccountSummary(?ConnectedAccount $connectedAccount): ?array
    {
        if (!$connectedAccount) {
            return null;
        }

        return [
            'id' => $connectedAccount->id,
            'provider' => $connectedAccount->provider,
            'provider_account_id' => $connectedAccount->provider_account_id,
            'status' => $connectedAccount->status,
            'charges_enabled' => (bool) $connectedAccount->charges_enabled,
            'payouts_enabled' => (bool) $connectedAccount->payouts_enabled,
            'details_submitted' => (bool) $connectedAccount->details_submitted,
            'capabilities' => is_array($connectedAccount->capabilities) ? $connectedAccount->capabilities : [],
            'metadata' => is_array($connectedAccount->metadata) ? $connectedAccount->metadata : [],
        ];
    }
}
