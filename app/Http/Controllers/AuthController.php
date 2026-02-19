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
            'country_code' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
        ]);

        // Geo-Fence Logic (Sovereign Shield)
        $geoService = new \App\Services\GeoIPService();
        if (!$geoService->validateRegion($request->ip(), $request->country_code)) {
             throw ValidationException::withMessages([
                'country_code' => ['Regional mismatch detected. Sovereign Geo-Fence protection activated.'],
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country_code' => $request->country_code,
        ]);

        // Create Default Tenant with regional settings
        $user->tenant()->create([
            'name' => "{$user->name}'s Node",
            'slug' => \Illuminate\Support\Str::slug($user->name),
            'region' => $request->country_code === 'BR' ? 'BR' : ($request->country_code === 'EU' ? 'EU' : 'Global'),
            'currency' => $request->currency,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
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
