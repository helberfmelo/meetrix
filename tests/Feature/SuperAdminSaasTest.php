<?php

namespace Tests\Feature;

use App\Models\BillingTransaction;
use App\Models\User;
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
