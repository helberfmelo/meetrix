<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'country_code' => 'nullable|string|size:2',
            'currency' => 'nullable|string|size:3',
            'timezone' => 'nullable|timezone',
            'preferred_locale' => 'nullable|string|max:10',
        ]);

        $countryCode = $request->country_code ?? 'BR';
        $currency = $request->currency ?? 'BRL';

        // Geo-Fence Logic (Sovereign Shield) - Only if enabled in config
        if (config('pricing.geoip_enabled')) {
            $geoService = new \App\Services\GeoIPService();
            if (!$geoService->validateRegion($request->ip(), $countryCode)) {
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
            'timezone' => $request->timezone ?? 'UTC',
            'preferred_locale' => $request->preferred_locale,
            'is_active' => true,
        ]);

        // Create Default Tenant with regional settings
        $user->tenant()->create([
            'name' => "{$user->name}'s Node",
            'slug' => \Illuminate\Support\Str::slug($user->name) . '-' . rand(100, 999), // Ensure unique slug for tenant
            'region' => $countryCode === 'BR' ? 'BR' : ($countryCode === 'EU' ? 'EU' : 'Global'),
            'currency' => $currency,
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
