<?php

namespace App\Http\Controllers;

use App\Services\GeoPricingCatalogService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly GeoPricingCatalogService $geoPricingCatalogService
    ) {
    }

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
            'onboarding_completed_at' => Carbon::now(),
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
            'message' => 'Onboarding completed successfully',
            'user' => $user->fresh(),
        ]);
    }
}
