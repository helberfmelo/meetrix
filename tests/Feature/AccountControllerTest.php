<?php

namespace Tests\Feature;

use App\Models\BillingTransaction;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_account_summary_and_billing_history(): void
    {
        $user = User::factory()->create([
            'subscription_tier' => 'pro',
            'preferred_locale' => 'pt-BR',
            'timezone' => 'America/Sao_Paulo',
        ]);

        BillingTransaction::create([
            'user_id' => $user->id,
            'source' => 'subscription',
            'status' => 'paid',
            'amount' => 49.90,
            'currency' => 'BRL',
            'description' => 'Mensalidade',
            'paid_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $summaryResponse = $this->getJson('/api/account/summary');
        $summaryResponse->assertStatus(200)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('billing_summary.paid_count', 1);

        $historyResponse = $this->getJson('/api/account/billing-history');
        $historyResponse->assertStatus(200)
            ->assertJsonPath('data.0.description', 'Mensalidade');
    }

    public function test_authenticated_user_can_change_subscription_and_register_billing_event(): void
    {
        $user = User::factory()->create([
            'subscription_tier' => 'free',
            'billing_cycle' => 'monthly',
            'account_mode' => 'scheduling_only',
            'region' => 'BR',
            'currency' => 'BRL',
            'platform_fee_percent' => 0,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/account/subscription/change', [
            'plan' => 'pro',
            'interval' => 'monthly',
            'reason' => 'upgrade',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.subscription_tier', 'pro')
            ->assertJsonPath('user.account_mode', 'scheduling_with_payments')
            ->assertJsonPath('billing_event.status', 'pending');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_code' => 'pro',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('billing_transactions', [
            'user_id' => $user->id,
            'source' => 'subscription',
            'status' => 'pending',
        ]);
    }

    public function test_authenticated_user_can_cancel_subscription_and_return_to_schedule_only(): void
    {
        $user = User::factory()->create([
            'subscription_tier' => 'pro',
            'billing_cycle' => 'annual',
            'account_mode' => 'scheduling_with_payments',
            'currency' => 'USD',
            'platform_fee_percent' => 1.25,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'plan_code' => 'pro',
            'billing_cycle' => 'annual',
            'account_mode' => 'scheduling_with_payments',
            'provider' => 'stripe',
            'price' => 108,
            'currency' => 'USD',
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'current_period_start' => now()->subMonth(),
            'current_period_end' => now()->addMonth(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/account/subscription/cancel', [
            'reason' => 'customer_request',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.subscription_tier', 'free')
            ->assertJsonPath('user.account_mode', 'scheduling_only')
            ->assertJsonPath('billing_event.status', 'cancelled');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('billing_transactions', [
            'user_id' => $user->id,
            'source' => 'subscription',
            'status' => 'cancelled',
        ]);
    }

    public function test_subscription_change_returns_error_code_when_request_is_noop(): void
    {
        $user = User::factory()->create([
            'subscription_tier' => 'pro',
            'billing_cycle' => 'monthly',
            'account_mode' => 'scheduling_with_payments',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/account/subscription/change', [
            'plan' => 'pro',
            'interval' => 'monthly',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error_code', 'subscription_plan_noop');
    }
}
