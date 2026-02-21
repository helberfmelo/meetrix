<?php

namespace Tests\Feature;

use App\Models\BillingTransaction;
use App\Models\SchedulingPage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountModeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_complete_persists_mode_and_preserves_existing_data(): void
    {
        $user = User::factory()->create([
            'account_mode' => 'scheduling_only',
            'region' => 'BR',
            'currency' => 'BRL',
            'platform_fee_percent' => 0,
        ]);

        Tenant::create([
            'user_id' => $user->id,
            'name' => 'Mode Tenant',
            'slug' => 'mode-tenant-' . rand(1000, 9999),
            'region' => 'BR',
            'currency' => 'BRL',
            'account_mode' => 'scheduling_only',
            'platform_fee_percent' => 0,
        ]);

        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'slug' => 'mode-preserve-page',
            'title' => 'Mode Preserve Page',
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/onboarding/complete', [
            'account_mode' => 'scheduling_with_payments',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.account_mode', 'scheduling_with_payments');

        $user->refresh();
        $this->assertNotNull($user->onboarding_completed_at);
        $this->assertSame('scheduling_with_payments', $user->account_mode);

        $this->assertDatabaseHas('tenants', [
            'user_id' => $user->id,
            'account_mode' => 'scheduling_with_payments',
        ]);

        $this->assertDatabaseHas('scheduling_pages', [
            'id' => $page->id,
            'user_id' => $user->id,
            'slug' => 'mode-preserve-page',
        ]);
    }

    public function test_account_mode_endpoint_upgrades_without_removing_existing_page(): void
    {
        $user = User::factory()->create([
            'account_mode' => 'scheduling_only',
            'region' => 'USD',
            'currency' => 'USD',
            'platform_fee_percent' => 0,
        ]);

        Tenant::create([
            'user_id' => $user->id,
            'name' => 'Mode Tenant',
            'slug' => 'mode-upgrade-' . rand(1000, 9999),
            'region' => 'USD',
            'currency' => 'USD',
            'account_mode' => 'scheduling_only',
            'platform_fee_percent' => 0,
        ]);

        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'slug' => 'account-upgrade-page',
            'title' => 'Account Upgrade Page',
            'is_active' => true,
        ]);

        $transaction = BillingTransaction::create([
            'user_id' => $user->id,
            'source' => 'subscription',
            'status' => 'paid',
            'amount' => 9,
            'currency' => 'USD',
            'description' => 'Legacy subscription record',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/account/mode', [
            'account_mode' => 'scheduling_with_payments',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.account_mode', 'scheduling_with_payments');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_mode' => 'scheduling_with_payments',
        ]);

        $this->assertDatabaseHas('scheduling_pages', [
            'id' => $page->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('billing_transactions', [
            'id' => $transaction->id,
            'user_id' => $user->id,
            'description' => 'Legacy subscription record',
        ]);
    }

    public function test_scheduling_only_account_forces_free_appointment_types(): void
    {
        $user = User::factory()->create([
            'account_mode' => 'scheduling_only',
            'currency' => 'BRL',
        ]);

        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'slug' => 'free-only-page',
            'title' => 'Free Only Page',
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/pages/{$page->id}/appointment-types", [
            'name' => 'Paid Attempt',
            'duration_minutes' => 30,
            'price' => 199.90,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('price', '0.00')
            ->assertJsonPath('currency', 'BRL');
    }

    public function test_scheduling_with_payments_account_keeps_paid_appointment_value(): void
    {
        $user = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
            'currency' => 'USD',
        ]);

        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'slug' => 'payments-page',
            'title' => 'Payments Page',
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/pages/{$page->id}/appointment-types", [
            'name' => 'Paid Session',
            'duration_minutes' => 45,
            'price' => 120,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('price', '120.00')
            ->assertJsonPath('currency', 'USD');
    }
}
