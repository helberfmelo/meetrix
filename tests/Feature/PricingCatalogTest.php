<?php

namespace Tests\Feature;

use Database\Seeders\GeoPricingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeoPricingSeeder::class);
    }

    public function test_catalog_returns_brazil_matrix_with_brl_currency(): void
    {
        $response = $this->getJson('/api/pricing/catalog?country_code=BR');

        $response->assertOk()
            ->assertJsonPath('region', 'BR')
            ->assertJsonPath('currency', 'BRL')
            ->assertJsonPath('plans.scheduling_only.monthly_price', 29)
            ->assertJsonPath('plans.scheduling_with_payments.platform_fee_percent', 2.5);
    }

    public function test_catalog_returns_usd_matrix_for_north_america_countries(): void
    {
        $response = $this->getJson('/api/pricing/catalog?country_code=US');

        $response->assertOk()
            ->assertJsonPath('region', 'USD')
            ->assertJsonPath('currency', 'USD')
            ->assertJsonPath('plans.scheduling_only.monthly_price', 7)
            ->assertJsonPath('plans.scheduling_with_payments.monthly_price', 9)
            ->assertJsonPath('plans.scheduling_with_payments.platform_fee_percent', 1.25);
    }

    public function test_catalog_uses_locale_fallback_when_country_is_missing(): void
    {
        $response = $this->getJson('/api/pricing/catalog?locale=pt-BR');

        $response->assertOk()
            ->assertJsonPath('region', 'BR');

        $fallbackResponse = $this->getJson('/api/pricing/catalog?locale=fr-FR');

        $fallbackResponse->assertOk()
            ->assertJsonPath('region', 'EUR')
            ->assertJsonPath('currency', 'EUR');
    }
}
