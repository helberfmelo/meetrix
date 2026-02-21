<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentFeature;
use App\Services\Payments\StripeConnectService;
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

        try {
            $result = $this->stripeConnectService->createAccountLink($user);

            return response()->json([
                'status' => 'ok',
                'account_link_url' => $result['url'],
                'expires_at' => $result['expires_at'],
                'connected_account' => $result['connected_account'],
            ]);
        } catch (ApiErrorException $exception) {
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

    public function status(Request $request)
    {
        $user = $request->user();
        $connectedAccount = $this->stripeConnectService->getActiveConnectedAccount($user)
            ?? $user->connectedAccounts()
                ->where('provider', 'stripe_connect')
                ->latest()
                ->first();

        return response()->json([
            'payments_enabled_for_user' => $this->paymentFeature->isEnabledForUser($user),
            'payments_enabled_global' => $this->paymentFeature->isGloballyEnabled(),
            'account_mode' => $user->account_mode,
            'connected_account' => $connectedAccount,
        ]);
    }
}
