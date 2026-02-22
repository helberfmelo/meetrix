<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\BillingTransaction;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\PricingOperationalFee;
use App\Models\PricingPlatformCommission;
use App\Models\SchedulingPage;
use App\Models\Team;
use App\Models\User;
use App\Support\ApiError;
use App\Support\FinancialObservability;
use App\Services\FinancialKpiService;
use App\Services\GeoPricingCatalogService;
use App\Services\PricingFeeEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaasAdminController extends Controller
{
    public function __construct(
        private readonly FinancialKpiService $financialKpiService,
        private readonly GeoPricingCatalogService $geoPricingCatalogService,
        private readonly PricingFeeEngineService $pricingFeeEngineService
    ) {
    }

    /**
     * High-level SaaS metrics and operational snapshots.
     */
    public function overview(Request $request)
    {
        $clientsQuery = User::query()->where('is_super_admin', false);
        $financialSnapshot = $this->financialKpiService->snapshot($request->boolean('refresh_kpis', false));

        $currentMonthRevenue = BillingTransaction::query()
            ->where('status', 'paid')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');

        $couponSummary = Coupon::query()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active, SUM(times_used) as uses')
            ->first();

        return response()->json([
            'kpis' => [
                'clients_total' => (clone $clientsQuery)->count(),
                'clients_active' => (clone $clientsQuery)->where('is_active', true)->count(),
                'clients_inactive' => (clone $clientsQuery)->where('is_active', false)->count(),
                'teams_total' => Team::count(),
                'pages_total' => SchedulingPage::count(),
                'bookings_total' => Booking::count(),
                'payments_paid_total' => BillingTransaction::where('status', 'paid')->count(),
                'revenue_current_month' => (float) $currentMonthRevenue,
                'revenue_converted_brl' => (float) ($financialSnapshot['revenue_converted_brl'] ?? 0),
                'paid_appointments_rate' => (float) data_get($financialSnapshot, 'paid_appointments.rate', 0),
                'mode_upgrade_rate' => (float) data_get($financialSnapshot, 'mode_upgrade.rate', 0),
            ],
            'subscriptions' => $clientsQuery
                ->selectRaw("COALESCE(subscription_tier, 'free') as tier, COUNT(*) as total")
                ->groupBy('tier')
                ->pluck('total', 'tier'),
            'coupons' => [
                'total' => (int) ($couponSummary->total ?? 0),
                'active' => (int) ($couponSummary->active ?? 0),
                'uses' => (int) ($couponSummary->uses ?? 0),
            ],
            'recent_activity' => AdminActivityLog::with(['actor:id,name,email', 'target:id,name,email'])
                ->latest()
                ->limit(12)
                ->get(),
            'recent_payments' => BillingTransaction::with('user:id,name,email')
                ->latest()
                ->limit(12)
                ->get(),
            'financial' => $financialSnapshot,
        ]);
    }

    /**
     * Paginated customers list with operational filters.
     */
    public function customers(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
            'subscription' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = User::query()
            ->where('is_super_admin', false)
            ->with('tenant')
            ->withCount([
                'schedulingPages',
                'ownedTeams',
                'billingTransactions as payments_paid_count' => function ($q) {
                    $q->where('status', 'paid');
                },
                'billingTransactions as payments_pending_count' => function ($q) {
                    $q->where('status', 'pending');
                },
            ])
            ->latest();

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($tenantQuery) use ($search) {
                        $tenantQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
            });
        }

        if (($validated['status'] ?? 'all') === 'active') {
            $query->where('is_active', true);
        } elseif (($validated['status'] ?? 'all') === 'inactive') {
            $query->where('is_active', false);
        }

        if (!empty($validated['subscription']) && $validated['subscription'] !== 'all') {
            $query->where('subscription_tier', $validated['subscription']);
        }

        $perPage = $validated['per_page'] ?? 20;

        return response()->json($query->paginate($perPage));
    }

    /**
     * Full detail view for a specific customer account.
     */
    public function showCustomer(User $user)
    {
        if ($user->is_super_admin) {
            return ApiError::response(
                'Conta de sistema nao disponivel para operacao.',
                'customer_system_account_not_available',
                404
            );
        }

        $user->load([
            'tenant',
            'integrations',
            'ownedTeams.users:id,name,email',
            'schedulingPages:id,user_id,team_id,slug,title,is_active,views,slot_clicks,created_at',
        ]);

        $bookings = Booking::query()
            ->whereHas('schedulingPage', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['schedulingPage:id,slug,title', 'appointmentType:id,name,duration_minutes'])
            ->latest('start_at')
            ->limit(25)
            ->get();

        return response()->json([
            'customer' => $user,
            'billing_transactions' => $user->billingTransactions()->latest()->limit(25)->get(),
            'bookings' => $bookings,
            'activity' => $user->adminTargetActivities()
                ->with('actor:id,name,email')
                ->latest()
                ->limit(25)
                ->get(),
            'coupon_snapshot' => Coupon::latest()->limit(25)->get(),
        ]);
    }

    /**
     * Execute secure administrative actions on customer account.
     */
    public function performAction(Request $request, User $user)
    {
        if ($user->is_super_admin) {
            return ApiError::response(
                'Acao indisponivel para conta de sistema.',
                'customer_system_action_blocked',
                422
            );
        }

        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'reset_onboarding'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['action'] === 'deactivate' && $request->user()->id === $user->id) {
            return ApiError::response(
                'Voce nao pode desativar sua propria conta administrativa.',
                'admin_self_deactivation_forbidden',
                422
            );
        }

        if ($validated['action'] === 'activate') {
            $user->update(['is_active' => true]);
        }

        if ($validated['action'] === 'deactivate') {
            $user->update(['is_active' => false]);
            $user->tokens()->delete();
        }

        if ($validated['action'] === 'reset_onboarding') {
            $user->update(['onboarding_completed_at' => null]);
        }

        $this->logActivity($request, $user, $validated['action'], [
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'message' => 'Ação administrativa aplicada com sucesso.',
            'customer' => $user->fresh(),
        ]);
    }

    /**
     * Paginated audit trail for admin actions.
     */
    public function activity(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 25), 100);

        $activity = AdminActivityLog::query()
            ->with(['actor:id,name,email', 'target:id,name,email'])
            ->latest()
            ->paginate($perPage);

        return response()->json($activity);
    }

    /**
     * Paginated payments view for SaaS operations.
     */
    public function payments(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['all', 'pending', 'paid', 'failed', 'cancelled', 'refunded'])],
            'source' => ['nullable', Rule::in(['all', 'subscription', 'booking', 'manual'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = BillingTransaction::query()->with(['user:id,name,email', 'booking:id,scheduling_page_id,start_at,status']);

        if (($validated['status'] ?? 'all') !== 'all') {
            $query->where('status', $validated['status']);
        }

        if (($validated['source'] ?? 'all') !== 'all') {
            $query->where('source', $validated['source']);
        }

        $perPage = $validated['per_page'] ?? 25;

        return response()->json($query->latest()->paginate($perPage));
    }

    /**
     * Execute financial actions for operational billing handling.
     */
    public function paymentAction(Request $request, BillingTransaction $transaction)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['retry_payment', 'manual_adjustment'])],
            'reason' => ['nullable', 'string', 'max:500'],
            'amount' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
            'adjustment_type' => ['nullable', Rule::in(['credit', 'debit'])],
        ]);

        $action = (string) $validated['action'];

        if ($action === 'retry_payment') {
            if (!in_array($transaction->status, ['failed', 'cancelled'], true)) {
                FinancialObservability::warning(
                    'super_admin.payment_action',
                    'payment_retry_not_allowed',
                    'Tentativa de reprocessamento em status invalido.',
                    [
                        'transaction_id' => $transaction->id,
                        'status' => $transaction->status,
                        'requested_by' => $request->user()->id,
                    ]
                );

                return ApiError::response(
                    'Reprocessamento permitido apenas para pagamentos failed/cancelled.',
                    'payment_retry_not_allowed',
                    422,
                    ['status' => $transaction->status]
                );
            }

            try {
                $metadata = is_array($transaction->metadata) ? $transaction->metadata : [];
                $retryCount = (int) data_get($metadata, 'retry.count', 0) + 1;
                $metadata['retry'] = [
                    'count' => $retryCount,
                    'requested_by' => $request->user()->id,
                    'reason' => $validated['reason'] ?? null,
                    'requested_at' => now()->toIso8601String(),
                ];

                $transaction->update([
                    'status' => 'pending',
                    'metadata' => $metadata,
                    'paid_at' => null,
                ]);

                $this->logActivity($request, $transaction->user, 'payment_retry', [
                    'billing_transaction_id' => $transaction->id,
                    'reason' => $validated['reason'] ?? null,
                    'retry_count' => $retryCount,
                ]);

                return response()->json([
                    'message' => 'Pagamento marcado para reprocessamento.',
                    'transaction' => $transaction->fresh(),
                ]);
            } catch (\Throwable $exception) {
                FinancialObservability::error(
                    'super_admin.payment_action',
                    'payment_retry_failed',
                    $exception->getMessage(),
                    [
                        'transaction_id' => $transaction->id,
                        'requested_by' => $request->user()->id,
                    ]
                );

                return ApiError::response(
                    'Falha ao preparar o reprocessamento. Tente novamente.',
                    'payment_retry_failed',
                    500
                );
            }
        }

        if (empty($validated['amount']) || empty($validated['adjustment_type']) || empty($validated['reason'])) {
            FinancialObservability::warning(
                'super_admin.payment_action',
                'manual_adjustment_missing_fields',
                'Ajuste manual sem campos obrigatorios.',
                [
                    'transaction_id' => $transaction->id,
                    'requested_by' => $request->user()->id,
                ]
            );

            return ApiError::response(
                'Ajuste manual exige valor, tipo e motivo.',
                'manual_adjustment_missing_fields',
                422
            );
        }

        $amount = round((float) $validated['amount'], 2);
        if ($amount <= 0) {
            FinancialObservability::warning(
                'super_admin.payment_action',
                'manual_adjustment_invalid_amount',
                'Ajuste manual com valor invalido.',
                [
                    'transaction_id' => $transaction->id,
                    'requested_by' => $request->user()->id,
                    'amount' => $validated['amount'],
                ]
            );

            return ApiError::response(
                'Valor de ajuste deve ser maior que zero.',
                'manual_adjustment_invalid_amount',
                422
            );
        }

        $direction = (string) $validated['adjustment_type'];
        $signedAmount = $direction === 'credit' ? -1 * $amount : $amount;

        try {
            $adjustment = BillingTransaction::create([
                'user_id' => $transaction->user_id,
                'booking_id' => $transaction->booking_id,
                'source' => 'manual',
                'status' => 'paid',
                'amount' => $signedAmount,
                'currency' => $transaction->currency,
                'description' => "Ajuste manual ({$direction}) para transacao #{$transaction->id}",
                'metadata' => [
                    'event' => 'manual_adjustment',
                    'reference_transaction_id' => $transaction->id,
                    'adjustment_type' => $direction,
                    'reason' => $validated['reason'],
                    'requested_by' => $request->user()->id,
                ],
                'paid_at' => now(),
            ]);

            $this->logActivity($request, $transaction->user, 'manual_adjustment', [
                'billing_transaction_id' => $transaction->id,
                'manual_adjustment_id' => $adjustment->id,
                'amount' => $signedAmount,
                'reason' => $validated['reason'],
            ]);

            return response()->json([
                'message' => 'Ajuste manual aplicado com sucesso.',
                'manual_adjustment' => $adjustment,
                'reference_transaction' => $transaction->fresh(),
            ]);
        } catch (\Throwable $exception) {
            FinancialObservability::error(
                'super_admin.payment_action',
                'manual_adjustment_failed',
                $exception->getMessage(),
                [
                    'transaction_id' => $transaction->id,
                    'requested_by' => $request->user()->id,
                    'adjustment_type' => $direction,
                    'amount' => $signedAmount,
                ]
            );

            return ApiError::response(
                'Falha ao aplicar ajuste manual. Tente novamente.',
                'manual_adjustment_failed',
                500
            );
        }
    }

    /**
     * Export billing transactions as CSV for financial operations.
     */
    public function exportPaymentsCsv(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['all', 'pending', 'paid', 'failed', 'cancelled', 'refunded'])],
            'source' => ['nullable', Rule::in(['all', 'subscription', 'booking', 'manual'])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $query = BillingTransaction::query()->with('user:id,name,email');

        if (($validated['status'] ?? 'all') !== 'all') {
            $query->where('status', $validated['status']);
        }

        if (($validated['source'] ?? 'all') !== 'all') {
            $query->where('source', $validated['source']);
        }

        if (!empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if (!empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        $filename = 'meetrix-payments-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'transaction_id',
                'user_id',
                'user_name',
                'user_email',
                'source',
                'status',
                'amount',
                'currency',
                'description',
                'created_at',
                'paid_at',
                'external_reference',
            ]);

            $query->orderBy('id')->chunk(500, function ($transactions) use ($handle) {
                foreach ($transactions as $transaction) {
                    fputcsv($handle, [
                        $transaction->id,
                        $transaction->user_id,
                        $transaction->user?->name,
                        $transaction->user?->email,
                        $transaction->source,
                        $transaction->status,
                        $transaction->amount,
                        $transaction->currency,
                        $transaction->description,
                        optional($transaction->created_at)?->toDateTimeString(),
                        optional($transaction->paid_at)?->toDateTimeString(),
                        $transaction->external_reference,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Current pricing matrix and locale-currency bindings for master admin.
     */
    public function pricingSettings()
    {
        return response()->json($this->buildPricingSettingsPayload());
    }

    /**
     * Persist pricing matrix + locale-currency bindings from master admin.
     */
    public function updatePricingSettings(Request $request)
    {
        $supportedRegions = $this->geoPricingCatalogService->supportedRegions();
        $supportedCurrencies = $this->geoPricingCatalogService->supportedCurrencies();
        $supportedAccountModes = $this->geoPricingCatalogService->supportedAccountModes();

        $validated = $request->validate([
            'plans' => ['required', 'array', 'min:1'],
            'plans.*.region_code' => ['required', Rule::in($supportedRegions)],
            'plans.*.currency' => ['required', Rule::in($supportedCurrencies)],
            'plans.*.account_mode' => ['required', Rule::in($supportedAccountModes)],
            'plans.*.monthly_price' => ['required', 'numeric', 'min:0'],
            'plans.*.annual_price' => ['required', 'numeric', 'min:0'],
            'plans.*.platform_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'plans.*.premium_price' => ['nullable', 'numeric', 'min:0'],
            'plans.*.premium_fee_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'plans.*.label' => ['nullable', 'string', 'max:120'],
            'plans.*.plan_code' => ['nullable', 'string', 'max:64'],
            'plans.*.is_active' => ['nullable', 'boolean'],
            'locale_currency_map' => ['required', 'array', 'min:1'],
            'locale_currency_map.*.locale_code' => ['required', 'string', 'max:16'],
            'locale_currency_map.*.currency' => ['required', Rule::in($supportedCurrencies)],
            'locale_currency_map.*.is_active' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['plans'] as $plan) {
            $regionCode = strtoupper((string) $plan['region_code']);
            $currency = strtoupper((string) $plan['currency']);
            $expectedCurrency = $this->geoPricingCatalogService->currencyForRegion($regionCode);

            if ($currency !== $expectedCurrency) {
                return ApiError::response(
                    "Moeda invalida para regiao {$regionCode}. Use {$expectedCurrency}.",
                    'pricing_region_currency_mismatch',
                    422,
                    [
                        'region_code' => $regionCode,
                        'currency' => $currency,
                        'expected_currency' => $expectedCurrency,
                    ]
                );
            }
        }

        $localeRows = [];
        foreach ($validated['locale_currency_map'] as $mapping) {
            $localeCode = $this->geoPricingCatalogService->normalizeLocaleCode((string) $mapping['locale_code']);
            if ($localeCode === null) {
                continue;
            }

            $currency = strtoupper((string) $mapping['currency']);
            $localeRows[$localeCode] = [
                'locale_code' => $localeCode,
                'region_code' => $this->geoPricingCatalogService->regionForCurrency($currency),
                'currency' => $currency,
                'is_active' => (bool) ($mapping['is_active'] ?? true),
            ];
        }

        if ($localeRows === []) {
            throw ValidationException::withMessages([
                'locale_currency_map' => ['Informe ao menos um idioma valido para mapeamento de moeda.'],
            ]);
        }

        DB::transaction(function () use ($validated, $localeRows, $request) {
            foreach ($validated['plans'] as $plan) {
                $regionCode = strtoupper((string) $plan['region_code']);
                $accountMode = (string) $plan['account_mode'];
                $currency = strtoupper((string) $plan['currency']);

                $existingMetadataRaw = DB::table('geo_pricing')
                    ->where('region_code', $regionCode)
                    ->where('account_mode', $accountMode)
                    ->value('metadata');

                $existingMetadata = is_array($existingMetadataRaw)
                    ? $existingMetadataRaw
                    : json_decode((string) $existingMetadataRaw, true);
                $metadata = is_array($existingMetadata) ? $existingMetadata : [];

                $metadata['label'] = $plan['label'] ?? ($metadata['label'] ?? null);
                $metadata['plan_code'] = $plan['plan_code']
                    ?? ($metadata['plan_code'] ?? ($accountMode === 'scheduling_only' ? 'schedule_starter' : 'payments_pro'));

                if ($accountMode === 'scheduling_with_payments') {
                    $metadata['premium_price'] = $plan['premium_price'] ?? null;
                    $metadata['premium_fee_percent'] = $plan['premium_fee_percent'] ?? null;
                } else {
                    unset($metadata['premium_price'], $metadata['premium_fee_percent']);
                }

                DB::table('geo_pricing')->updateOrInsert(
                    [
                        'region_code' => $regionCode,
                        'account_mode' => $accountMode,
                    ],
                    [
                        'currency' => $currency,
                        'monthly_price' => round((float) $plan['monthly_price'], 2),
                        'annual_price' => round((float) $plan['annual_price'], 2),
                        'platform_fee_percent' => round((float) $plan['platform_fee_percent'], 2),
                        'is_active' => (bool) ($plan['is_active'] ?? true),
                        'metadata' => json_encode($metadata),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            DB::table('pricing_locale_currency_maps')
                ->whereNotIn('locale_code', array_keys($localeRows))
                ->delete();

            foreach ($localeRows as $localeRow) {
                DB::table('pricing_locale_currency_maps')->updateOrInsert(
                    ['locale_code' => $localeRow['locale_code']],
                    [
                        'region_code' => $localeRow['region_code'],
                        'currency' => $localeRow['currency'],
                        'is_active' => $localeRow['is_active'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            Log::info('Super admin pricing settings updated.', [
                'actor_user_id' => $request->user()->id,
                'plans_count' => count($validated['plans']),
                'locale_map_count' => count($localeRows),
            ]);
        });

        return response()->json([
            'message' => 'Configuracoes de planos atualizadas com sucesso.',
            ...$this->buildPricingSettingsPayload(),
        ]);
    }

    /**
     * Current platform commission and operational fee matrix for master admin.
     */
    public function pricingFeeSettings()
    {
        return response()->json($this->buildPricingFeeSettingsPayload());
    }

    /**
     * Persist platform commission + operational fee settings in bulk.
     */
    public function updatePricingFeeSettings(Request $request)
    {
        $supportedCurrencies = $this->pricingFeeEngineService->supportedCurrencies();
        $supportedPaymentMethods = $this->pricingFeeEngineService->supportedPaymentMethods();
        $supportedPlanCodes = $this->pricingFeeEngineService->supportedPlanCodes();

        $validated = $request->validate([
            'commissions' => ['required', 'array'],
            'commissions.*.plan_code' => ['required', 'string', Rule::in($supportedPlanCodes)],
            'commissions.*.currency' => ['required', Rule::in($supportedCurrencies)],
            'commissions.*.payment_method' => ['required', Rule::in($supportedPaymentMethods)],
            'commissions.*.commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'commissions.*.is_active' => ['nullable', 'boolean'],
            'operational_fees' => ['required', 'array'],
            'operational_fees.*.currency' => ['required', Rule::in($supportedCurrencies)],
            'operational_fees.*.payment_method' => ['required', Rule::in($supportedPaymentMethods)],
            'operational_fees.*.fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'operational_fees.*.is_active' => ['nullable', 'boolean'],
        ]);

        $commissionRows = [];
        foreach ($validated['commissions'] as $row) {
            $planCode = strtolower(trim((string) $row['plan_code']));
            $currency = strtoupper(trim((string) $row['currency']));
            $paymentMethod = strtolower(trim((string) $row['payment_method']));

            if (!$this->isPaymentMethodAllowedForCurrency($currency, $paymentMethod)) {
                return ApiError::response(
                    "Forma de pagamento {$paymentMethod} nao permitida para moeda {$currency}.",
                    'pricing_fee_payment_method_currency_mismatch',
                    422,
                    [
                        'currency' => $currency,
                        'payment_method' => $paymentMethod,
                    ]
                );
            }

            $key = "{$planCode}|{$currency}|{$paymentMethod}";
            $commissionRows[$key] = [
                'plan_code' => $planCode,
                'currency' => $currency,
                'payment_method' => $paymentMethod,
                'commission_percent' => round((float) $row['commission_percent'], 2),
                'is_active' => (bool) ($row['is_active'] ?? true),
            ];
        }

        $operationalRows = [];
        foreach ($validated['operational_fees'] as $row) {
            $currency = strtoupper(trim((string) $row['currency']));
            $paymentMethod = strtolower(trim((string) $row['payment_method']));

            if (!$this->isPaymentMethodAllowedForCurrency($currency, $paymentMethod)) {
                return ApiError::response(
                    "Forma de pagamento {$paymentMethod} nao permitida para moeda {$currency}.",
                    'pricing_fee_payment_method_currency_mismatch',
                    422,
                    [
                        'currency' => $currency,
                        'payment_method' => $paymentMethod,
                    ]
                );
            }

            $key = "{$currency}|{$paymentMethod}";
            $operationalRows[$key] = [
                'currency' => $currency,
                'payment_method' => $paymentMethod,
                'fee_percent' => round((float) $row['fee_percent'], 2),
                'is_active' => (bool) ($row['is_active'] ?? true),
            ];
        }

        DB::transaction(function () use ($commissionRows, $operationalRows, $request) {
            $keptCommissionIds = [];
            foreach ($commissionRows as $row) {
                $record = PricingPlatformCommission::query()->updateOrCreate(
                    [
                        'plan_code' => $row['plan_code'],
                        'currency' => $row['currency'],
                        'payment_method' => $row['payment_method'],
                    ],
                    [
                        'commission_percent' => $row['commission_percent'],
                        'is_active' => $row['is_active'],
                    ]
                );
                $keptCommissionIds[] = $record->id;
            }

            if ($keptCommissionIds === []) {
                PricingPlatformCommission::query()->delete();
            } else {
                PricingPlatformCommission::query()
                    ->whereNotIn('id', $keptCommissionIds)
                    ->delete();
            }

            $keptOperationalFeeIds = [];
            foreach ($operationalRows as $row) {
                $record = PricingOperationalFee::query()->updateOrCreate(
                    [
                        'currency' => $row['currency'],
                        'payment_method' => $row['payment_method'],
                    ],
                    [
                        'fee_percent' => $row['fee_percent'],
                        'is_active' => $row['is_active'],
                    ]
                );
                $keptOperationalFeeIds[] = $record->id;
            }

            if ($keptOperationalFeeIds === []) {
                PricingOperationalFee::query()->delete();
            } else {
                PricingOperationalFee::query()
                    ->whereNotIn('id', $keptOperationalFeeIds)
                    ->delete();
            }

            Log::info('Super admin pricing fee settings updated.', [
                'actor_user_id' => $request->user()->id,
                'commissions_count' => count($commissionRows),
                'operational_fees_count' => count($operationalRows),
            ]);
        });

        return response()->json([
            'message' => 'Configuracoes de taxas atualizadas com sucesso.',
            ...$this->buildPricingFeeSettingsPayload(),
        ]);
    }

    /**
     * Calculate fee composition for a plan/currency/payment method combination.
     */
    public function calculatePricingFeeComposition(Request $request)
    {
        $supportedCurrencies = $this->pricingFeeEngineService->supportedCurrencies();
        $supportedPaymentMethods = $this->pricingFeeEngineService->supportedPaymentMethods();
        $supportedPlanCodes = $this->pricingFeeEngineService->supportedPlanCodes();

        $validated = $request->validate([
            'plan_code' => ['required', 'string', Rule::in($supportedPlanCodes)],
            'currency' => ['required', Rule::in($supportedCurrencies)],
            'payment_method' => ['required', Rule::in($supportedPaymentMethods)],
            'gross_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $currency = strtoupper(trim((string) $validated['currency']));
        $paymentMethod = strtolower(trim((string) $validated['payment_method']));

        if (!$this->isPaymentMethodAllowedForCurrency($currency, $paymentMethod)) {
            return ApiError::response(
                "Forma de pagamento {$paymentMethod} nao permitida para moeda {$currency}.",
                'pricing_fee_payment_method_currency_mismatch',
                422,
                [
                    'currency' => $currency,
                    'payment_method' => $paymentMethod,
                ]
            );
        }

        $composition = $this->pricingFeeEngineService->calculate(
            (string) $validated['plan_code'],
            $currency,
            $paymentMethod,
            isset($validated['gross_amount']) ? (float) $validated['gross_amount'] : null
        );

        return response()->json([
            'composition' => $composition,
        ]);
    }

    /**
     * Coupon visibility for super admin operations.
     */
    public function coupons()
    {
        return response()->json([
            'coupons' => Coupon::latest()->get(),
        ]);
    }

    /**
     * Mail configuration diagnostics for production operations.
     */
    public function mailDiagnostics()
    {
        $defaultMailer = (string) config('mail.default', 'log');
        $smtpConfig = (array) config('mail.mailers.smtp', []);
        $fromAddress = (string) config('mail.from.address');

        $riskFlags = [];

        if ($defaultMailer !== 'smtp') {
            $riskFlags[] = 'default_mailer_not_smtp';
        }

        if (empty($smtpConfig['username']) || empty($smtpConfig['password'])) {
            $riskFlags[] = 'smtp_credentials_missing';
        }

        if (in_array((string) ($smtpConfig['host'] ?? ''), ['127.0.0.1', 'localhost'], true)) {
            $riskFlags[] = 'smtp_host_default_local';
        }

        if ($fromAddress === '' || str_ends_with($fromAddress, '@example.com')) {
            $riskFlags[] = 'from_address_not_configured';
        }

        return response()->json([
            'default_mailer' => $defaultMailer,
            'from' => [
                'address' => $fromAddress,
                'name' => (string) config('mail.from.name'),
            ],
            'smtp' => [
                'host' => (string) ($smtpConfig['host'] ?? ''),
                'port' => (int) ($smtpConfig['port'] ?? 0),
                'encryption' => (string) ($smtpConfig['encryption'] ?? ''),
                'username_configured' => !empty($smtpConfig['username']),
                'password_configured' => !empty($smtpConfig['password']),
            ],
            'risk_flags' => $riskFlags,
        ]);
    }

    /**
     * Send a controlled e-mail from super admin for SMTP validation.
     */
    public function sendTestEmail(Request $request)
    {
        $availableMailers = array_keys((array) config('mail.mailers', []));

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'mailer' => ['nullable', Rule::in($availableMailers)],
        ]);

        $mailer = $validated['mailer']
            ?? (in_array('smtp', $availableMailers, true) ? 'smtp' : (string) config('mail.default', 'log'));

        try {
            $sentMessage = Mail::mailer($mailer)->raw(
                'Teste de envio Meetrix. Este e-mail confirma conectividade SMTP do ambiente.',
                function ($message) use ($validated) {
                    $message->to($validated['email'])->subject('Meetrix - Teste de E-mail');
                }
            );

            $messageId = $sentMessage?->getMessageId();
            Log::info('Super admin mail test sent.', [
                'recipient' => $validated['email'],
                'mailer' => $mailer,
                'message_id' => $messageId,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Super admin mail test failed: ' . $exception->getMessage());

            return response()->json([
                'message' => 'Falha ao enviar e-mail de teste.',
                'mailer' => $mailer,
                'error' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'E-mail de teste enviado para a fila de transporte.',
            'mailer' => $mailer,
            'recipient' => $validated['email'],
            'message_id' => $messageId ?? null,
        ]);
    }

    /**
     * Shared payload used by pricing settings endpoints.
     *
     * @return array{
     *     regions:array<int, array<string,mixed>>,
     *     locale_currency_map:array<int, array<string,mixed>>,
     *     supported:array<string, array<int,string>>
     * }
     */
    private function buildPricingSettingsPayload(): array
    {
        return [
            'regions' => $this->geoPricingCatalogService->getGeoPricingMatrix(),
            'locale_currency_map' => $this->geoPricingCatalogService->getLocaleCurrencyMappings(),
            'supported' => [
                'regions' => $this->geoPricingCatalogService->supportedRegions(),
                'currencies' => $this->geoPricingCatalogService->supportedCurrencies(),
                'account_modes' => $this->geoPricingCatalogService->supportedAccountModes(),
            ],
        ];
    }

    /**
     * @return array{
     *     commissions:array<int, array<string,mixed>>,
     *     operational_fees:array<int, array<string,mixed>>,
     *     supported:array<string, mixed>
     * }
     */
    private function buildPricingFeeSettingsPayload(): array
    {
        $operationalFees = Schema::hasTable('pricing_operational_fees')
            ? PricingOperationalFee::query()
                ->orderBy('currency')
                ->orderBy('payment_method')
                ->get()
                ->map(function (PricingOperationalFee $row) {
                    return [
                        'id' => $row->id,
                        'currency' => strtoupper((string) $row->currency),
                        'payment_method' => strtolower((string) $row->payment_method),
                        'fee_percent' => (float) $row->fee_percent,
                        'is_active' => (bool) $row->is_active,
                        'updated_at' => optional($row->updated_at)?->toIso8601String(),
                    ];
                })
                ->values()
                ->all()
            : [];

        return [
            'commissions' => $this->pricingFeeEngineService->commissionsWithComposition(),
            'operational_fees' => $operationalFees,
            'supported' => [
                'currencies' => $this->pricingFeeEngineService->supportedCurrencies(),
                'payment_methods' => $this->pricingFeeEngineService->supportedPaymentMethods(),
                'payment_method_labels' => $this->pricingFeeEngineService->paymentMethodLabels(),
                'payment_methods_by_currency' => $this->pricingFeeEngineService->supportedPaymentMethodsByCurrency(),
                'plan_codes' => $this->pricingFeeEngineService->supportedPlanCodes(),
            ],
        ];
    }

    private function isPaymentMethodAllowedForCurrency(string $currency, string $paymentMethod): bool
    {
        $methodsByCurrency = $this->pricingFeeEngineService->supportedPaymentMethodsByCurrency();
        $allowedMethods = $methodsByCurrency[strtoupper($currency)] ?? [];

        return in_array(strtolower($paymentMethod), $allowedMethods, true);
    }

    private function logActivity(Request $request, User $target, string $action, array $details = []): void
    {
        AdminActivityLog::create([
            'actor_user_id' => $request->user()->id,
            'target_user_id' => $target->id,
            'action' => $action,
            'details' => $details,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
