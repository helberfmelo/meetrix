<?php

namespace Tests\Feature;

use App\Models\ConnectedAccount;
use App\Models\User;
use App\Services\Payments\StripeConnectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class PaymentConnectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_embedded_session_returns_client_secret_for_eligible_user(): void
    {
        config()->set('payments.enabled', true);

        $user = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
        ]);

        $connectedAccount = ConnectedAccount::create([
            'user_id' => $user->id,
            'provider' => 'stripe_connect',
            'provider_account_id' => 'acct_test_123',
            'status' => 'pending',
            'charges_enabled' => false,
            'payouts_enabled' => false,
            'details_submitted' => false,
            'capabilities' => ['card_payments' => 'inactive'],
            'metadata' => [],
        ]);

        $this->mock(StripeConnectService::class, function (MockInterface $mock) use ($user, $connectedAccount): void {
            $mock->shouldReceive('createEmbeddedOnboardingSession')
                ->once()
                ->withArgs(fn (User $resolvedUser) => $resolvedUser->id === $user->id)
                ->andReturn([
                    'client_secret' => 'sess_client_secret_test',
                    'expires_at' => now()->addMinutes(30)->timestamp,
                    'connected_account' => $connectedAccount,
                ]);

            $mock->shouldReceive('isReceivingReady')
                ->once()
                ->withArgs(fn ($resolvedAccount) => $resolvedAccount?->id === $connectedAccount->id)
                ->andReturn(false);
        });

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/payments/connect/embedded/session');

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('client_secret', 'sess_client_secret_test')
            ->assertJsonPath('onboarding_required', true)
            ->assertJsonPath('receiving_ready', false)
            ->assertJsonPath('connected_account_summary.provider_account_id', 'acct_test_123');
    }

    public function test_embedded_session_blocks_scheduling_only_mode(): void
    {
        config()->set('payments.enabled', true);

        $user = User::factory()->create([
            'account_mode' => 'scheduling_only',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/payments/connect/embedded/session');

        $response->assertStatus(422)
            ->assertJsonPath('error_code', 'account_mode_not_supported');
    }

    public function test_embedded_session_blocks_user_outside_payments_rollout(): void
    {
        config()->set('payments.enabled', false);
        config()->set('payments.rollout_user_ids', []);

        $user = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/payments/connect/embedded/session');

        $response->assertStatus(409)
            ->assertJsonPath('error_code', 'payments_feature_disabled');
    }

    public function test_connect_status_does_not_require_onboarding_for_scheduling_only_mode(): void
    {
        config()->set('payments.enabled', true);

        $user = User::factory()->create([
            'account_mode' => 'scheduling_only',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/payments/connect/status');

        $response->assertOk()
            ->assertJsonPath('account_mode', 'scheduling_only')
            ->assertJsonPath('onboarding_required', false)
            ->assertJsonPath('receiving_ready', false);
    }

    public function test_connect_status_requires_onboarding_for_payments_mode_when_not_ready(): void
    {
        config()->set('payments.enabled', true);

        $user = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
        ]);

        ConnectedAccount::create([
            'user_id' => $user->id,
            'provider' => 'stripe_connect',
            'provider_account_id' => null,
            'status' => 'pending',
            'charges_enabled' => false,
            'payouts_enabled' => false,
            'details_submitted' => false,
            'capabilities' => ['card_payments' => 'inactive'],
            'metadata' => [],
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/payments/connect/status');

        $response->assertOk()
            ->assertJsonPath('account_mode', 'scheduling_with_payments')
            ->assertJsonPath('onboarding_required', true)
            ->assertJsonPath('receiving_ready', false)
            ->assertJsonPath('connected_account_summary.status', 'pending');
    }

    public function test_connect_status_marks_receiving_ready_when_connected_account_is_active(): void
    {
        config()->set('payments.enabled', true);

        $user = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
        ]);

        ConnectedAccount::create([
            'user_id' => $user->id,
            'provider' => 'stripe_connect',
            'provider_account_id' => 'acct_ready_123',
            'status' => 'active',
            'charges_enabled' => true,
            'payouts_enabled' => true,
            'details_submitted' => true,
            'capabilities' => ['card_payments' => 'active', 'transfers' => 'active'],
            'metadata' => [],
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/payments/connect/status');

        $response->assertOk()
            ->assertJsonPath('receiving_ready', true)
            ->assertJsonPath('onboarding_required', false)
            ->assertJsonPath('connected_account_summary.provider_account_id', 'acct_ready_123');
    }
}
