<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\BillingTransaction;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\SchedulingPage;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class SaasAdminController extends Controller
{
    /**
     * High-level SaaS metrics and operational snapshots.
     */
    public function overview()
    {
        $clientsQuery = User::query()->where('is_super_admin', false);

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
        abort_if($user->is_super_admin, 404, 'Conta de sistema não disponível.');

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
        abort_if($user->is_super_admin, 422, 'Ação indisponível para conta de sistema.');

        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'reset_onboarding'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['action'] === 'deactivate' && $request->user()->id === $user->id) {
            return response()->json([
                'message' => 'Você não pode desativar sua própria conta administrativa.',
            ], 422);
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
