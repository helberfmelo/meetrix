<?php

namespace App\Http\Controllers;

use App\Services\GeoPricingCatalogService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly GeoPricingCatalogService $geoPricingCatalogService
    ) {
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'account_mode' => 'nullable|in:scheduling_only,scheduling_with_payments',
            'country_code' => 'nullable|string|size:2',
            'currency' => 'nullable|string|size:3',
            'timezone' => 'nullable|timezone',
            'preferred_locale' => 'nullable|string|max:10',
        ]);

        $providedCountryCode = $request->filled('country_code')
            ? strtoupper((string) $request->country_code)
            : null;
        $preferredLocale = $request->preferred_locale ?: $request->header('Accept-Language');
        $pricingContext = $this->geoPricingCatalogService->resolvePricingContext(
            $providedCountryCode,
            $preferredLocale,
            $request->ip()
        );

        $countryCode = $providedCountryCode ?? $pricingContext['detected_country_code'];
        $accountMode = $request->account_mode ?? 'scheduling_only';
        $region = $pricingContext['region'];
        $currency = strtoupper($request->currency ?? $pricingContext['currency']);

        // Geo-Fence Logic (Sovereign Shield) - Only if enabled in config
        if (config('pricing.geoip_enabled') && $providedCountryCode !== null) {
            $geoService = new \App\Services\GeoIPService();
            if (!$geoService->validateRegion($request->ip(), $providedCountryCode)) {
                throw ValidationException::withMessages([
                    'country_code' => ['Regional mismatch detected. Sovereign Geo-Fence protection activated.'],
                ]);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country_code' => $countryCode,
            'account_mode' => $accountMode,
            'region' => $region,
            'currency' => $currency,
            'platform_fee_percent' => 0,
            'timezone' => $request->timezone ?? 'UTC',
            'preferred_locale' => $request->preferred_locale,
            'is_active' => true,
        ]);

        // Create Default Tenant with regional settings
        $user->tenant()->create([
            'name' => "{$user->name}'s Node",
            'slug' => \Illuminate\Support\Str::slug($user->name) . '-' . rand(100, 999), // Ensure unique slug for tenant
            'region' => $region,
            'currency' => $currency,
            'account_mode' => $accountMode,
            'platform_fee_percent' => 0,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Conta desativada. Contate o suporte.'],
            ]);
        }

        $user->update([
            'last_login_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return $request->user();
    }
}
