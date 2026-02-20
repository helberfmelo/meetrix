<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Subscription Created Webhook.
     */
    public function test_subscription_created_webhook()
    {
        $user = User::factory()->create(['subscription_tier' => 'free']);

        $payload = [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'customer' => 'cus_test123',
                    'current_period_end' => strtotime('+1 month'),
                    'metadata' => [
                        'user_id' => $user->id,
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/webhooks/stripe', $payload);

        $response->assertStatus(200);
        $user->refresh();
        $this->assertEquals('pro', $user->subscription_tier);
        $this->assertEquals('cus_test123', $user->stripe_id);
    }

    /**
     * Test Subscription Deleted Webhook.
     */
    public function test_subscription_deleted_webhook()
    {
        $user = User::factory()->create([
            'subscription_tier' => 'pro',
            'stripe_id' => 'cus_test123'
        ]);

        $payload = [
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'metadata' => [
                        'user_id' => $user->id,
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/webhooks/stripe', $payload);

        $response->assertStatus(200);
        $user->refresh();
        $this->assertEquals('free', $user->subscription_tier);
    }
}
