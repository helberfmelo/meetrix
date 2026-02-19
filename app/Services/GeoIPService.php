<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIPService
{
    /**
     * Detect country code from IP using ip-api.com
     */
    public function getCountryCode(string $ip): ?string
    {
        // Skip local addresses for testing if needed
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'US'; // Default for local
        }

        try {
            $response = Http::get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['countryCode'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error("GeoIP lookup failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Validate if the provided country code matches the IP location.
     */
    public function validateRegion(string $ip, string $providedCountry): bool
    {
        if (!config('pricing.geoip_enabled')) {
            return true;
        }

        $detected = $this->getCountryCode($ip);
        
        if (!$detected) {
            return true; // Soft fail - don't block users if API is down
        }

        return strtoupper($detected) === strtoupper($providedCountry);
    }
}
