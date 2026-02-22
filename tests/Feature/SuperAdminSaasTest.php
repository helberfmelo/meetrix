<?php

namespace Tests\Feature;

use App\Models\BillingTransaction;
use App\Models\Booking;
use App\Models\SchedulingPage;
use App\Models\User;
use Database\Seeders\GeoPricingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuperAdminSaasTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_list_customers_and_apply_actions(): void
    {
        $admin = User::factory()->create([
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin);

        $customersResponse = $this->getJson('/api/super-admin/customers');
        $customersResponse->assertStatus(200)
            ->assertJsonFragment(['email' => $customer->email]);

        $actionResponse = $this->postJson("/api/super-admin/customers/{$customer->id}/actions", [
            'action' => 'deactivate',
            'reason' => 'teste',
        ]);

        $actionResponse->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('admin_activity_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $customer->id,
            'action' => 'deactivate',
        ]);
    }

    public function test_non_super_admin_cannot_access_super_admin_routes(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/super-admin/overview');

        $response->assertStatus(403);
    }

    public function test_super_admin_overview_exposes_financial_kpis_for_pr06(): void
    {
        config()->set('analytics.currency_to_brl', [
            'BRL' => 1,
            'USD' => 5,
            'EUR' => 6,
        ]);

        $admin = User::factory()->create([
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        $customerBr = User::factory()->create([
            'is_super_admin' => false,
            'is_active' => true,
            'country_code' => 'BR',
            'account_mode' => 'scheduling_only',
        ]);

        $customerUs = User::factory()->create([
            'is_super_admin' => false,
            'is_active' => true,
            'country_code' => 'US',
            'account_mode' => 'scheduling_with_payments',
        ]);

        $pageBr = SchedulingPage::create([
            'user_id' => $customerBr->id,
            'slug' => 'page-br-' . uniqid(),
            'title' => 'Page BR',
        ]);

        $pageUs = SchedulingPage::create([
            'user_id' => $customerUs->id,
            'slug' => 'page-us-' . uniqid(),
            'title' => 'Page US',
        ]);

        Booking::create([
            'scheduling_page_id' => $pageBr->id,
            'customer_name' => 'Cliente BR',
            'customer_email' => 'cliente-br@example.com',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addMinutes(30),
            'status' => 'confirmed',
            'is_paid' => true,
            'amount_paid' => 120,
        ]);

        Booking::create([
            'scheduling_page_id' => $pageUs->id,
            'customer_name' => 'Cliente US',
            'customer_email' => 'cliente-us@example.com',
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addMinutes(30),
            'status' => 'confirmed',
            'is_paid' => false,
            'amount_paid' => 0,
        ]);

        BillingTransaction::create([
            'user_id' => $customerBr->id,
            'source' => 'booking',
            'status' => 'paid',
            'amount' => 120,
            'currency' => 'BRL',
            'description' => 'Booking BR pago',
            'paid_at' => now(),
        ]);

        BillingTransaction::create([
            'user_id' => $customerUs->id,
            'source' => 'booking',
            'status' => 'paid',
            'amount' => 40,
            'currency' => 'USD',
            'description' => 'Booking US pago',
            'paid_at' => now(),
        ]);

        BillingTransaction::create([
            'user_id' => $customerUs->id,
            'source' => 'booking',
            'status' => 'failed',
            'amount' => 15,
            'currency' => 'USD',
            'description' => 'Falha gateway',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/super-admin/overview');

        $response->assertOk()
            ->assertJsonPath('kpis.revenue_converted_brl', 320)
            ->assertJsonPath('kpis.paid_appointments_rate', 50)
            ->assertJsonPath('kpis.mode_upgrade_rate', 50)
            ->assertJsonPath('financial.revenue_converted_brl', 320)
            ->assertJsonFragment(['currency' => 'USD'])
            ->assertJsonFragment(['country_code' => 'BR'])
            ->assertJsonFragment(['country_code' => 'US']);
    }

    public function test_super_admin_can_update_pricing_matrix_and_locale_currency_map(): void
    {
        $this->seed(GeoPricingSeeder::class);

        $admin = User::factory()->create([
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin);

        $settingsResponse = $this->getJson('/api/super-admin/pricing/settings');
        $settingsResponse->assertOk()
            ->assertJsonFragment(['region_code' => 'BR'])
            ->assertJsonFragment(['currency' => 'BRL']);

        $plansPayload = [];
        foreach ($settingsResponse->json('regions', []) as $region) {
            foreach ($region['plans'] as $plan) {
                $plansPayload[] = [
                    'region_code' => $region['region_code'],
                    'currency' => $region['currency'],
                    'account_mode' => $plan['account_mode'],
                    'monthly_price' => $region['region_code'] === 'BR' && $plan['account_mode'] === 'scheduling_only'
                        ? 35
                        : $plan['monthly_price'],
                    'annual_price' => $plan['annual_price'],
                    'platform_fee_percent' => $plan['platform_fee_percent'],
                    'premium_price' => $plan['premium_price'],
                    'premium_fee_percent' => $plan['premium_fee_percent'],
                    'label' => $plan['label'],
                    'plan_code' => $plan['plan_code'],
                    'is_active' => true,
                ];
            }
        }

        $updateResponse = $this->putJson('/api/super-admin/pricing/settings', [
            'plans' => $plansPayload,
            'locale_currency_map' => [
                ['locale_code' => 'pt-BR', 'currency' => 'BRL', 'is_active' => true],
                ['locale_code' => 'en', 'currency' => 'USD', 'is_active' => true],
                ['locale_code' => 'fr', 'currency' => 'EUR', 'is_active' => true],
            ],
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('regions.0.region_code', 'BR');

        $this->assertDatabaseHas('geo_pricing', [
            'region_code' => 'BR',
            'account_mode' => 'scheduling_only',
            'monthly_price' => 35,
        ]);

        $this->assertDatabaseHas('pricing_locale_currency_maps', [
            'locale_code' => 'fr',
            'currency' => 'EUR',
            'region_code' => 'EUR',
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_update_pricing_fee_settings_and_calculate_total(): void
    {
        $this->seed(GeoPricingSeeder::class);

        $admin = User::factory()->create([
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin);

        $settingsResponse = $this->getJson('/api/super-admin/pricing/fees');
        $settingsResponse->assertOk()
            ->assertJsonStructure([
                'commissions',
                'operational_fees',
                'supported' => [
                    'currencies',
                    'payment_methods',
                    'payment_method_labels',
                    'payment_methods_by_currency',
                    'plan_codes',
                ],
            ])
            ->assertJsonFragment([
                'plan_code' => 'payments_pro',
                'currency' => 'BRL',
                'payment_method' => 'card',
            ]);

        $updateResponse = $this->putJson('/api/super-admin/pricing/fees', [
            'commissions' => [
                [
                    'plan_code' => 'payments_pro',
                    'currency' => 'BRL',
                    'payment_method' => 'card',
                    'commission_percent' => 4.20,
                    'is_active' => true,
                ],
                [
                    'plan_code' => 'payments_premium',
                    'currency' => 'BRL',
                    'payment_method' => 'pix',
                    'commission_percent' => 1.10,
                    'is_active' => true,
                ],
            ],
            'operational_fees' => [
                [
                    'currency' => 'BRL',
                    'payment_method' => 'card',
                    'fee_percent' => 1.80,
                    'is_active' => true,
                ],
                [
                    'currency' => 'BRL',
                    'payment_method' => 'pix',
                    'fee_percent' => 0.90,
                    'is_active' => true,
                ],
            ],
        ]);

        $updateResponse->assertOk()
            ->assertJsonFragment([
                'plan_code' => 'payments_pro',
                'currency' => 'BRL',
                'payment_method' => 'card',
                'commission_percent' => 4.2,
            ]);

        $this->assertDatabaseHas('pricing_platform_commissions', [
            'plan_code' => 'payments_pro',
            'currency' => 'BRL',
            'payment_method' => 'card',
            'commission_percent' => 4.20,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('pricing_operational_fees', [
            'currency' => 'BRL',
            'payment_method' => 'card',
            'fee_percent' => 1.80,
            'is_active' => true,
        ]);

        $this->assertDatabaseMissing('pricing_operational_fees', [
            'currency' => 'USD',
            'payment_method' => 'card',
        ]);

        $compositionResponse = $this->postJson('/api/super-admin/pricing/fees/calculate', [
            'plan_code' => 'payments_pro',
            'currency' => 'BRL',
            'payment_method' => 'card',
            'gross_amount' => 100,
        ]);

        $compositionResponse->assertOk()
            ->assertJsonPath('composition.commission_percent', 4.2)
            ->assertJsonPath('composition.operational_fee_percent', 1.8)
            ->assertJsonPath('composition.total_fee_percent', 6)
            ->assertJsonPath('composition.total_fee_amount', 6)
            ->assertJsonPath('composition.net_amount', 94);
    }

    public function test_super_admin_can_check_mail_diagnostics_and_send_test_mail(): void
    {
        $admin = User::factory()->create([
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin);

        $diagnosticsResponse = $this->getJson('/api/super-admin/mail/diagnostics');

        $diagnosticsResponse->assertStatus(200)
            ->assertJsonStructure([
                'default_mailer',
                'from' => ['address', 'name'],
                'smtp' => ['host', 'port', 'encryption', 'username_configured', 'password_configured'],
                'risk_flags',
            ]);

        $testMailResponse = $this->postJson('/api/super-admin/mail/test', [
            'email' => 'qa@meetrix.test',
            'mailer' => 'array',
        ]);

        $testMailResponse->assertStatus(200)
            ->assertJson([
                'mailer' => 'array',
                'recipient' => 'qa@meetrix.test',
            ]);
    }

    public function test_super_admin_can_retry_failed_payment_and_log_activity(): void
    {
        $admin = User::factory()->create([
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        $transaction = BillingTransaction::create([
            'user_id' => $customer->id,
            'source' => 'subscription',
            'status' => 'failed',
            'amount' => 39,
            'currency' => 'BRL',
            'description' => 'Falha no pagamento recorrente',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/super-admin/payments/{$transaction->id}/actions", [
            'action' => 'retry_payment',
            'reason' => 'gateway timeout',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('transaction.status', 'pending');

        $this->assertDatabaseHas('billing_transactions', [
            'id' => $transaction->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('admin_activity_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $customer->id,
            'action' => 'payment_retry',
        ]);
    }

    public function test_super_admin_can_create_manual_adjustment_and_log_activity(): void
    {
        $admin = User::factory()->create([
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        $referenceTransaction = BillingTransaction::create([
            'user_id' => $customer->id,
            'source' => 'booking',
            'status' => 'paid',
            'amount' => 150,
            'currency' => 'BRL',
            'description' => 'Pagamento confirmado',
            'paid_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/super-admin/payments/{$referenceTransaction->id}/actions", [
            'action' => 'manual_adjustment',
            'adjustment_type' => 'credit',
            'amount' => 15,
            'reason' => 'compensacao operacional',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('manual_adjustment.source', 'manual')
            ->assertJsonPath('manual_adjustment.status', 'paid');

        $this->assertDatabaseHas('billing_transactions', [
            'user_id' => $customer->id,
            'source' => 'manual',
            'status' => 'paid',
            'amount' => -15,
        ]);

        $this->assertDatabaseHas('admin_activity_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $customer->id,
            'action' => 'manual_adjustment',
        ]);
    }

    public function test_super_admin_can_export_payments_csv(): void
    {
        $admin = User::factory()->create([
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        BillingTransaction::create([
            'user_id' => $customer->id,
            'source' => 'subscription',
            'status' => 'paid',
            'amount' => 49.90,
            'currency' => 'BRL',
            'description' => 'Mensalidade export',
            'paid_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->get('/api/super-admin/payments/export?status=paid');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('transaction_id', $content);
        $this->assertStringContainsString('Mensalidade export', $content);
    }
}
