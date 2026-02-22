<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Services\GeoPricingCatalogService;
use App\Support\ApiError;
use App\Support\FinancialObservability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct(
        private readonly GeoPricingCatalogService $geoPricingCatalogService
    ) {
    }

    /**
     * Return account summary for authenticated user.
     */
    public function summary(Request $request)
    {
        $user = $request->user()->loadCount(['schedulingPages', 'teams']);
        $activeSubscription = $user->subscriptions()->latest()->first();
        $subscriptionOptions = $this->resolveSubscriptionOptions(
            $user,
            $request->header('Accept-Language'),
            $request->ip()
        );

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country_code' => $user->country_code,
                'account_mode' => $user->account_mode ?? 'scheduling_only',
                'region' => $user->region ?? 'BR',
                'currency' => $user->currency ?? 'BRL',
                'platform_fee_percent' => (float) ($user->platform_fee_percent ?? 0),
                'timezone' => $user->timezone ?? 'UTC',
                'preferred_locale' => $user->preferred_locale,
                'subscription_tier' => $user->subscription_tier ?? 'free',
                'billing_cycle' => $user->billing_cycle,
                'trial_ends_at' => $user->trial_ends_at,
                'subscription_ends_at' => $user->subscription_ends_at,
                'onboarding_completed_at' => $user->onboarding_completed_at,
                'last_login_at' => $user->last_login_at,
                'is_super_admin' => $user->is_super_admin,
                'is_active' => $user->is_active ?? true,
                'scheduling_pages_count' => $user->scheduling_pages_count,
                'teams_count' => $user->teams_count,
            ],
            'billing_summary' => [
                'total_paid' => (float) $user->billingTransactions()->where('status', 'paid')->sum('amount'),
                'paid_count' => $user->billingTransactions()->where('status', 'paid')->count(),
                'pending_count' => $user->billingTransactions()->where('status', 'pending')->count(),
            ],
            'recent_transactions' => $user->billingTransactions()
                ->latest()
                ->limit(10)
                ->get(),
            'active_subscription' => $activeSubscription,
            'subscription_options' => $subscriptionOptions,
        ]);
    }

    /**
     * Update personal profile fields.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'country_code' => ['nullable', 'string', 'size:2'],
        ]);

        $pricingContext = $this->geoPricingCatalogService->resolvePricingContext(
            $validated['country_code'] ?? null,
            $user->preferred_locale ?: $request->header('Accept-Language'),
            $request->ip()
        );

        $user->update([
            ...$validated,
            'region' => $pricingContext['region'],
            'currency' => $pricingContext['currency'],
        ]);

        $tenant = $user->tenant;
        if ($tenant) {
            $tenant->update([
                'region' => $pricingContext['region'],
                'currency' => $pricingContext['currency'],
            ]);
        }

        return response()->json([
            'message' => 'Perfil atualizado com sucesso.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'preferred_locale' => ['nullable', 'string', 'max:10'],
            'timezone' => ['required', 'timezone'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $pricingContext = $this->geoPricingCatalogService->resolvePricingContext(
            $user->country_code,
            $validated['preferred_locale'] ?? null,
            $request->ip()
        );

        $user->update([
            ...$validated,
            'region' => $pricingContext['region'],
            'currency' => $pricingContext['currency'],
        ]);

        $tenant = $user->tenant;
        if ($tenant) {
            $tenant->update([
                'region' => $pricingContext['region'],
                'currency' => $pricingContext['currency'],
            ]);
        }

        return response()->json([
            'message' => 'Preferências atualizadas com sucesso.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Upgrade or switch account operating mode.
     */
    public function updateMode(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'account_mode' => ['required', Rule::in(['scheduling_only', 'scheduling_with_payments'])],
        ]);

        $mode = $validated['account_mode'];
        $pricingContext = $this->geoPricingCatalogService->resolvePricingContext(
            $user->country_code,
            $user->preferred_locale ?: $request->header('Accept-Language'),
            $request->ip()
        );
        $region = $pricingContext['region'] ?? ($user->region ?? 'BR');
        $currency = $pricingContext['currency'] ?? ($user->currency ?? 'BRL');

        $fee = DB::table('geo_pricing')
            ->where('region_code', $region)
            ->where('account_mode', $mode)
            ->where('is_active', true)
            ->value('platform_fee_percent');

        $resolvedFee = $fee !== null ? (float) $fee : 0.0;

        $user->update([
            'account_mode' => $mode,
            'region' => $region,
            'currency' => $currency,
            'platform_fee_percent' => $resolvedFee,
        ]);

        $tenant = $user->tenant;
        if ($tenant) {
            $tenant->update([
                'account_mode' => $mode,
                'region' => $region,
                'currency' => $currency,
                'platform_fee_percent' => $resolvedFee,
            ]);
        }

        return response()->json([
            'message' => 'Modo de conta atualizado com sucesso.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Allow users to upgrade/downgrade subscription tier from account area.
     */
    public function changeSubscription(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'plan' => ['required', Rule::in(['free', 'pro', 'enterprise'])],
            'interval' => ['required', Rule::in(['monthly', 'annual'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $targetPlan = (string) $validated['plan'];
        $targetInterval = (string) $validated['interval'];
        $currentPlan = (string) ($user->subscription_tier ?? 'free');
        $currentInterval = (string) ($user->billing_cycle ?? 'monthly');

        if ($targetPlan === $currentPlan && $targetInterval === $currentInterval) {
            FinancialObservability::warning(
                'account.subscription.change',
                'subscription_plan_noop',
                'Tentativa de alteracao sem mudanca de plano/ciclo.',
                [
                    'user_id' => $user->id,
                    'plan' => $targetPlan,
                    'interval' => $targetInterval,
                ]
            );

            return ApiError::response(
                'A conta ja esta no plano e ciclo selecionados.',
                'subscription_plan_noop',
                422
            );
        }

        $options = $this->resolveSubscriptionOptions(
            $user,
            $request->header('Accept-Language'),
            $request->ip()
        );
        $planConfig = $options['plans'][$targetPlan] ?? null;

        if (!is_array($planConfig)) {
            FinancialObservability::warning(
                'account.subscription.change',
                'subscription_plan_not_available',
                'Plano indisponivel para a regiao da conta.',
                [
                    'user_id' => $user->id,
                    'plan' => $targetPlan,
                    'region' => $options['region'],
                ]
            );

            return ApiError::response(
                'Plano indisponivel para a regiao atual.',
                'subscription_plan_not_available',
                422,
                ['region' => $options['region']]
            );
        }

        $amount = (float) ($planConfig['prices'][$targetInterval] ?? 0);
        $nextAccountMode = (string) ($planConfig['account_mode'] ?? 'scheduling_only');
        $nextFeePercent = (float) ($planConfig['platform_fee_percent'] ?? 0);
        $nextCurrency = (string) ($planConfig['currency'] ?? ($user->currency ?? 'BRL'));
        $periodEnd = $targetPlan === 'free'
            ? now()
            : ($targetInterval === 'annual' ? now()->addYear() : now()->addMonth());

        DB::beginTransaction();

        try {
            $user->update([
                'subscription_tier' => $targetPlan,
                'billing_cycle' => $targetInterval,
                'account_mode' => $nextAccountMode,
                'region' => $options['region'],
                'currency' => $nextCurrency,
                'platform_fee_percent' => $nextFeePercent,
                'trial_ends_at' => null,
                'subscription_ends_at' => $targetPlan === 'free' ? null : $periodEnd,
            ]);

            $tenant = $user->tenant;
            if ($tenant) {
                $tenant->update([
                    'account_mode' => $nextAccountMode,
                    'region' => $options['region'],
                    'currency' => $nextCurrency,
                    'platform_fee_percent' => $nextFeePercent,
                ]);
            }

            $subscription = $user->subscriptions()->latest()->first();
            if (!$subscription) {
                $subscription = new Subscription([
                    'user_id' => $user->id,
                    'provider' => 'stripe',
                ]);
            }

            $subscription->fill([
                'plan_code' => $targetPlan,
                'billing_cycle' => $targetInterval,
                'account_mode' => $nextAccountMode,
                'price' => $amount,
                'currency' => $nextCurrency,
                'status' => $targetPlan === 'free' ? 'cancelled' : 'active',
                'started_at' => $subscription->started_at ?? now(),
                'current_period_start' => $targetPlan === 'free' ? $subscription->current_period_start : now(),
                'current_period_end' => $periodEnd,
                'canceled_at' => $targetPlan === 'free' ? now() : null,
                'metadata' => [
                    'source' => 'account_self_service',
                    'previous_plan' => $currentPlan,
                    'previous_interval' => $currentInterval,
                    'reason' => $validated['reason'] ?? null,
                    'region' => $options['region'],
                ],
            ]);
            $subscription->save();

            $billingEvent = $user->billingTransactions()->create([
                'source' => 'subscription',
                'status' => $targetPlan === 'free' ? 'cancelled' : 'pending',
                'amount' => $amount,
                'currency' => $nextCurrency,
                'description' => "Self-service: {$currentPlan} -> {$targetPlan} ({$targetInterval})",
                'metadata' => [
                    'event' => 'subscription_change',
                    'previous_plan' => $currentPlan,
                    'previous_interval' => $currentInterval,
                    'target_plan' => $targetPlan,
                    'target_interval' => $targetInterval,
                    'reason' => $validated['reason'] ?? null,
                ],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Plano atualizado com sucesso.',
                'user' => $user->fresh(),
                'subscription' => $subscription->fresh(),
                'billing_event' => $billingEvent,
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();

            FinancialObservability::error(
                'account.subscription.change',
                'subscription_change_failed',
                $exception->getMessage(),
                [
                    'user_id' => $user->id,
                    'plan' => $targetPlan,
                    'interval' => $targetInterval,
                ]
            );

            return ApiError::response(
                'Falha ao atualizar a assinatura. Tente novamente.',
                'subscription_change_failed',
                500
            );
        }
    }

    /**
     * Cancel active paid subscription and return account to scheduling-only mode.
     */
    public function cancelSubscription(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if (($user->subscription_tier ?? 'free') === 'free') {
            FinancialObservability::warning(
                'account.subscription.cancel',
                'subscription_not_active',
                'Cancelamento solicitado sem assinatura ativa.',
                [
                    'user_id' => $user->id,
                    'subscription_tier' => $user->subscription_tier,
                ]
            );

            return ApiError::response(
                'Nao existe assinatura ativa para cancelar.',
                'subscription_not_active',
                422
            );
        }

        DB::beginTransaction();

        try {
            $user->update([
                'subscription_tier' => 'free',
                'billing_cycle' => 'monthly',
                'account_mode' => 'scheduling_only',
                'platform_fee_percent' => 0,
                'subscription_ends_at' => null,
            ]);

            $tenant = $user->tenant;
            if ($tenant) {
                $tenant->update([
                    'account_mode' => 'scheduling_only',
                    'platform_fee_percent' => 0,
                ]);
            }

            $user->subscriptions()
                ->whereIn('status', ['active', 'past_due', 'pending'])
                ->update([
                    'status' => 'cancelled',
                    'canceled_at' => now(),
                    'current_period_end' => now(),
                ]);

            $billingEvent = $user->billingTransactions()->create([
                'source' => 'subscription',
                'status' => 'cancelled',
                'amount' => 0,
                'currency' => $user->currency ?? 'BRL',
                'description' => 'Cancelamento self-service de assinatura',
                'metadata' => [
                    'event' => 'subscription_cancelled',
                    'reason' => $validated['reason'] ?? null,
                ],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Assinatura cancelada com sucesso.',
                'user' => $user->fresh(),
                'billing_event' => $billingEvent,
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();

            FinancialObservability::error(
                'account.subscription.cancel',
                'subscription_cancel_failed',
                $exception->getMessage(),
                [
                    'user_id' => $user->id,
                ]
            );

            return ApiError::response(
                'Falha ao cancelar assinatura. Tente novamente.',
                'subscription_cancel_failed',
                500
            );
        }
    }

    /**
     * Update current authenticated user password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Senha atualizada com sucesso.',
        ]);
    }

    /**
     * Paginated billing history for the authenticated user.
     */
    public function billingHistory(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 20), 100);

        $transactions = $request->user()
            ->billingTransactions()
            ->with('booking:id,scheduling_page_id,start_at,status')
            ->latest()
            ->paginate($perPage);

        return response()->json($transactions);
    }

    /**
     * @return array{
     *     region:string,
     *     currency:string,
     *     plans:array<string, array<string, mixed>>,
     *     resolution:array<string,mixed>
     * }
     */
    private function resolveSubscriptionOptions(
        User $user,
        ?string $localeHint = null,
        ?string $ipAddress = null
    ): array
    {
        $pricingContext = $this->geoPricingCatalogService->resolvePricingContext(
            $user->country_code,
            $user->preferred_locale ?: $localeHint,
            $ipAddress
        );

        $catalog = $this->geoPricingCatalogService->getCatalogForRegion((string) ($pricingContext['region'] ?? $user->region ?? 'BR'));
        $paymentsPlan = (array) ($catalog['plans']['scheduling_with_payments'] ?? []);

        $currency = (string) ($catalog['currency'] ?? $pricingContext['currency'] ?? $user->currency ?? 'BRL');
        $proMonthly = (float) ($paymentsPlan['monthly_price'] ?? 39);
        $proAnnual = (float) ($paymentsPlan['annual_price'] ?? 31);
        $proFee = (float) ($paymentsPlan['platform_fee_percent'] ?? 2.5);

        $enterpriseMonthly = (float) ($paymentsPlan['premium_price'] ?? round($proMonthly * 2, 2));
        $enterpriseAnnual = round($enterpriseMonthly * 0.8, 2);
        $enterpriseFee = (float) ($paymentsPlan['premium_fee_percent'] ?? max(0, $proFee - 1));

        return [
            'region' => (string) ($catalog['region'] ?? 'BR'),
            'currency' => $currency,
            'resolution' => $pricingContext,
            'plans' => [
                'free' => [
                    'label' => 'Agenda',
                    'account_mode' => 'scheduling_only',
                    'platform_fee_percent' => 0.0,
                    'currency' => $currency,
                    'prices' => [
                        'monthly' => 0.0,
                        'annual' => 0.0,
                    ],
                ],
                'pro' => [
                    'label' => 'Agenda + cobranca',
                    'account_mode' => 'scheduling_with_payments',
                    'platform_fee_percent' => $proFee,
                    'currency' => $currency,
                    'prices' => [
                        'monthly' => $proMonthly,
                        'annual' => $proAnnual,
                    ],
                ],
                'enterprise' => [
                    'label' => 'Enterprise',
                    'account_mode' => 'scheduling_with_payments',
                    'platform_fee_percent' => $enterpriseFee,
                    'currency' => $currency,
                    'prices' => [
                        'monthly' => $enterpriseMonthly,
                        'annual' => $enterpriseAnnual,
                    ],
                ],
            ],
        ];
    }
}
