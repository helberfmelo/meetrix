<?php

namespace Tests\Unit;

use App\Models\BillingTransaction;
use App\Models\User;
use App\Services\FinancialKpiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FinancialKpiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_flags_missing_fx_without_breaking_response(): void
    {
        config()->set('analytics.currency_to_brl', [
            'BRL' => 1,
            'USD' => 5,
        ]);
        config()->set('analytics.kpi_cache_ttl_seconds', 0);

        $user = User::factory()->create([
            'is_super_admin' => false,
            'country_code' => 'GB',
            'account_mode' => 'scheduling_with_payments',
        ]);

        BillingTransaction::create([
            'user_id' => $user->id,
            'source' => 'booking',
            'status' => 'paid',
            'amount' => 10,
            'currency' => 'GBP',
            'description' => 'Pagamento em moeda sem taxa configurada',
            'paid_at' => now(),
        ]);

        $snapshot = app(FinancialKpiService::class)->snapshot(true);

        $this->assertSame(0.0, (float) $snapshot['revenue_converted_brl']);
        $this->assertContains('GBP', $snapshot['fx_missing_currencies']);
        $this->assertFalse((bool) $snapshot['degraded_mode']);
        $this->assertSame('GBP', $snapshot['revenue_by_currency'][0]['currency']);
        $this->assertSame(0.0, (float) $snapshot['revenue_by_currency'][0]['amount_brl']);
    }

    public function test_snapshot_uses_cache_and_allows_forced_refresh(): void
    {
        config()->set('analytics.currency_to_brl', [
            'BRL' => 1,
        ]);
        config()->set('analytics.kpi_cache_ttl_seconds', 300);

        Cache::flush();

        $user = User::factory()->create([
            'is_super_admin' => false,
            'country_code' => 'BR',
            'account_mode' => 'scheduling_with_payments',
        ]);

        BillingTransaction::create([
            'user_id' => $user->id,
            'source' => 'booking',
            'status' => 'paid',
            'amount' => 10,
            'currency' => 'BRL',
            'description' => 'Receita inicial',
            'paid_at' => now(),
        ]);

        $service = app(FinancialKpiService::class);

        $firstSnapshot = $service->snapshot();
        $this->assertSame(10.0, (float) $firstSnapshot['revenue_converted_brl']);

        BillingTransaction::create([
            'user_id' => $user->id,
            'source' => 'booking',
            'status' => 'paid',
            'amount' => 15,
            'currency' => 'BRL',
            'description' => 'Receita posterior',
            'paid_at' => now(),
        ]);

        $cachedSnapshot = $service->snapshot();
        $this->assertSame(10.0, (float) $cachedSnapshot['revenue_converted_brl']);

        $refreshedSnapshot = $service->snapshot(true);
        $this->assertSame(25.0, (float) $refreshedSnapshot['revenue_converted_brl']);
    }
}
