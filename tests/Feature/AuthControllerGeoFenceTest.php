<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerGeoFenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_blocks_when_country_code_mismatches_detected_geoip_region(): void
    {
        config()->set('pricing.geoip_enabled', true);

        $response = $this->postJson('/api/register', [
            'name' => 'Geo Fence User',
            'email' => 'geo-fence-mismatch@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_mode' => 'scheduling_with_payments',
            'country_code' => 'BR',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code'])
            ->assertJsonPath('errors.country_code.0', 'Regional mismatch detected. Sovereign Geo-Fence protection activated.');
    }

    public function test_register_without_country_code_uses_pricing_resolution_and_is_not_blocked_by_geofence(): void
    {
        config()->set('pricing.geoip_enabled', true);

        $response = $this->postJson('/api/register', [
            'name' => 'Geo Fence Safe User',
            'email' => 'geo-fence-safe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_mode' => 'scheduling_with_payments',
            'timezone' => 'UTC',
            'preferred_locale' => 'en-US',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'geo-fence-safe@example.com',
            'account_mode' => 'scheduling_with_payments',
        ]);
    }
}
