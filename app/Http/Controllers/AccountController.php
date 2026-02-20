<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Return account summary for authenticated user.
     */
    public function summary(Request $request)
    {
        $user = $request->user()->loadCount(['schedulingPages', 'teams']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country_code' => $user->country_code,
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

        $user->update($validated);

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

        $request->user()->update($validated);

        return response()->json([
            'message' => 'Preferências atualizadas com sucesso.',
            'user' => $request->user()->fresh(),
        ]);
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
}
