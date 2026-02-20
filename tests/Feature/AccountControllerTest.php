<?php

namespace Tests\Feature;

use App\Models\BillingTransaction;
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
}
