<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    /**
     * Mark onboarding as completed for the authenticated user.
     */
    public function complete(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'account_mode' => ['required', Rule::in(['scheduling_only', 'scheduling_with_payments'])],
        ]);

        $mode = $validated['account_mode'];
        $region = $user->region ?? 'BR';
        $fee = DB::table('geo_pricing')
            ->where('region_code', $region)
            ->where('account_mode', $mode)
            ->where('is_active', true)
            ->value('platform_fee_percent');

        $resolvedFee = $fee !== null ? (float) $fee : 0.0;

        $user->update([
            'onboarding_completed_at' => Carbon::now(),
            'account_mode' => $mode,
            'platform_fee_percent' => $resolvedFee,
        ]);

        $tenant = $user->tenant;
        if ($tenant) {
            $tenant->update([
                'account_mode' => $mode,
                'platform_fee_percent' => $resolvedFee,
            ]);
        }

        return response()->json([
            'message' => 'Onboarding completed successfully',
            'user' => $user->fresh(),
        ]);
    }
}
