<?php

namespace Tests\Unit;

use App\Services\GeoPricingCatalogService;
use Database\Seeders\GeoPricingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeoPricingCatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_region_by_country_code(): void
    {
        $service = new GeoPricingCatalogService();

        $this->assertSame('BR', $service->resolveRegion('BR', null));
        $this->assertSame('USD', $service->resolveRegion('US', null));
        $this->assertSame('USD', $service->resolveRegion('CA', null));
        $this->assertSame('USD', $service->resolveRegion('AU', null));
        $this->assertSame('EUR', $service->resolveRegion('PT', null));
    }

    public function test_resolve_region_by_locale_when_country_is_absent(): void
    {
        $service = new GeoPricingCatalogService();

        $this->assertSame('BR', $service->resolveRegion(null, 'pt-BR'));
        $this->assertSame('USD', $service->resolveRegion(null, 'en-US'));
        $this->assertSame('EUR', $service->resolveRegion(null, 'es'));
    }

    public function test_catalog_returns_active_plans_and_falls_back_to_eur(): void
    {
        $this->seed(GeoPricingSeeder::class);

        $service = new GeoPricingCatalogService();

        $catalogBr = $service->getCatalogForRegion('BR');
        $this->assertSame('BR', $catalogBr['region']);
        $this->assertSame('BRL', $catalogBr['currency']);
        $this->assertSame(29.0, $catalogBr['plans']['scheduling_only']['monthly_price']);
        $this->assertSame(2.5, $catalogBr['plans']['scheduling_with_payments']['platform_fee_percent']);

        $catalogFallback = $service->getCatalogForRegion('UNKNOWN');
        $this->assertSame('EUR', $catalogFallback['region']);
        $this->assertSame('EUR', $catalogFallback['currency']);
    }
}
