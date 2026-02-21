<?php

namespace Tests\Feature;

use App\Models\AppointmentType;
use App\Models\BillingTransaction;
use App\Models\Booking;
use App\Models\ConnectedAccount;
use App\Models\Payment;
use App\Models\SchedulingPage;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookFinancialTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_intent_succeeded_enforces_signature_and_idempotency(): void
    {
        config()->set('payments.stripe.webhook_secret', 'whsec_test');

        [$user, $booking, $transaction] = $this->createBookingFinancialContext();
        $connectedAccount = ConnectedAccount::create([
            'user_id' => $user->id,
            'provider' => 'stripe_connect',
            'provider_account_id' => 'acct_123',
            'status' => 'active',
            'charges_enabled' => true,
            'payouts_enabled' => true,
            'details_submitted' => true,
        ]);

        $payload = [
            'id' => 'evt_pi_success_1',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_success_1',
                    'status' => 'succeeded',
                    'amount_received' => 15000,
                    'currency' => 'brl',
                    'latest_charge' => 'ch_success_1',
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'booking_id' => (string) $booking->id,
                        'transaction_id' => (string) $transaction->id,
                        'platform_fee_percent' => '2.50',
                        'platform_fee_amount_cents' => '375',
                        'net_amount_cents' => '14625',
                        'connected_account_id' => (string) $connectedAccount->id,
                        'connected_account_ref' => 'acct_123',
                    ],
                ],
            ],
        ];

        $response = $this->postSignedWebhook($payload);
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('payment_intents', [
            'provider_intent_id' => 'pi_success_1',
            'status' => 'succeeded',
        ]);

        $this->assertDatabaseHas('payments', [
            'provider_payment_id' => 'ch_success_1',
            'status' => 'paid',
            'booking_id' => $booking->id,
        ]);

        $this->assertDatabaseHas('billing_transactions', [
            'id' => $transaction->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
            'is_paid' => true,
        ]);

        $duplicate = $this->postSignedWebhook($payload);
        $duplicate->assertStatus(200)->assertJsonPath('status', 'duplicated');

        $this->assertEquals(1, Payment::where('provider_payment_id', 'ch_success_1')->count());
        $this->assertEquals(1, \App\Models\WebhookEvent::where('event_id', 'evt_pi_success_1')->count());
    }

    public function test_payment_intent_failed_marks_transaction_and_payment_as_failed(): void
    {
        config()->set('payments.stripe.webhook_secret', 'whsec_test');

        [$user, $booking, $transaction] = $this->createBookingFinancialContext();

        $payload = [
            'id' => 'evt_pi_failed_1',
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_failed_1',
                    'status' => 'requires_payment_method',
                    'amount' => 15000,
                    'currency' => 'brl',
                    'last_payment_error' => [
                        'code' => 'card_declined',
                        'message' => 'Card declined',
                    ],
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'booking_id' => (string) $booking->id,
                        'transaction_id' => (string) $transaction->id,
                    ],
                ],
            ],
        ];

        $response = $this->postSignedWebhook($payload);
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('payment_intents', [
            'provider_intent_id' => 'pi_failed_1',
            'status' => 'requires_payment_method',
            'last_error_code' => 'card_declined',
        ]);

        $this->assertDatabaseHas('payments', [
            'provider_intent_id' => 'pi_failed_1',
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('billing_transactions', [
            'id' => $transaction->id,
            'status' => 'failed',
        ]);
    }

    public function test_invoice_webhooks_update_subscription_lifecycle(): void
    {
        config()->set('payments.stripe.webhook_secret', 'whsec_test');

        $user = User::factory()->create([
            'subscription_tier' => 'free',
            'account_mode' => 'scheduling_with_payments',
            'currency' => 'USD',
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_code' => 'pro',
            'billing_cycle' => 'monthly',
            'account_mode' => 'scheduling_with_payments',
            'provider' => 'stripe',
            'price' => 0,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $successPayload = [
            'id' => 'evt_invoice_paid_1',
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'object' => [
                    'id' => 'in_paid_1',
                    'subscription' => 'sub_123',
                    'customer' => 'cus_123',
                    'amount_paid' => 900,
                    'currency' => 'usd',
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'plan' => 'pro',
                        'interval' => 'monthly',
                        'subscription_record_id' => (string) $subscription->id,
                    ],
                    'lines' => [
                        'data' => [[
                            'period' => [
                                'start' => now()->subDay()->timestamp,
                                'end' => now()->addMonth()->timestamp,
                            ],
                        ]],
                    ],
                ],
            ],
        ];

        $this->postSignedWebhook($successPayload)->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'provider_subscription_id' => 'sub_123',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('payments', [
            'provider_payment_id' => 'in_paid_1',
            'status' => 'paid',
            'subscription_id' => $subscription->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'subscription_tier' => 'pro',
            'billing_cycle' => 'monthly',
            'stripe_id' => 'cus_123',
        ]);

        $failedPayload = [
            'id' => 'evt_invoice_failed_1',
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'in_failed_1',
                    'subscription' => 'sub_123',
                    'customer' => 'cus_123',
                    'amount_due' => 900,
                    'currency' => 'usd',
                    'metadata' => [
                        'user_id' => (string) $user->id,
                    ],
                ],
            ],
        ];

        $this->postSignedWebhook($failedPayload)->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'provider_subscription_id' => 'sub_123',
            'status' => 'past_due',
        ]);

        $this->assertDatabaseHas('payments', [
            'provider_payment_id' => 'in_failed_1',
            'status' => 'failed',
        ]);
    }

    public function test_charge_refunded_event_marks_payment_and_transaction_refunded(): void
    {
        config()->set('payments.stripe.webhook_secret', 'whsec_test');

        $user = User::factory()->create();

        $payment = Payment::create([
            'user_id' => $user->id,
            'provider' => 'stripe',
            'provider_payment_id' => 'ch_refund_1',
            'provider_intent_id' => 'pi_refund_1',
            'amount' => 150,
            'currency' => 'BRL',
            'platform_fee_percent' => 2.5,
            'platform_fee_amount' => 3.75,
            'net_amount' => 146.25,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $transaction = BillingTransaction::create([
            'user_id' => $user->id,
            'source' => 'booking',
            'status' => 'paid',
            'external_reference' => 'ch_refund_1',
            'amount' => 150,
            'currency' => 'BRL',
            'description' => 'Pagamento de teste',
            'paid_at' => now(),
        ]);

        $payload = [
            'id' => 'evt_charge_refunded_1',
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'id' => 'ch_refund_1',
                    'payment_intent' => 'pi_refund_1',
                    'amount_refunded' => 15000,
                    'currency' => 'brl',
                    'metadata' => [
                        'transaction_id' => (string) $transaction->id,
                    ],
                ],
            ],
        ];

        $this->postSignedWebhook($payload)->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
        ]);

        $this->assertDatabaseHas('billing_transactions', [
            'id' => $transaction->id,
            'status' => 'refunded',
        ]);
    }

    private function createBookingFinancialContext(): array
    {
        $user = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
            'platform_fee_percent' => 2.5,
            'currency' => 'BRL',
        ]);

        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'slug' => 'webhook-financial-context-' . $user->id,
            'title' => 'Webhook Financial Context',
            'is_active' => true,
        ]);

        $type = AppointmentType::create([
            'scheduling_page_id' => $page->id,
            'name' => 'Sessao Premium',
            'duration_minutes' => 60,
            'price' => 150,
            'currency' => 'BRL',
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'scheduling_page_id' => $page->id,
            'appointment_type_id' => $type->id,
            'customer_name' => 'Cliente Webhook',
            'customer_email' => 'cliente-webhook@example.com',
            'customer_timezone' => 'UTC',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'status' => 'pending',
        ]);

        $transaction = BillingTransaction::create([
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'source' => 'booking',
            'status' => 'pending',
            'amount' => 150,
            'currency' => 'BRL',
            'description' => 'Pagamento pendente de teste',
        ]);

        return [$user, $booking, $transaction];
    }

    private function postSignedWebhook(array $payload)
    {
        $secret = (string) config('payments.stripe.webhook_secret');
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $json, $secret);
        $header = "t={$timestamp},v1={$signature}";

        return $this->call(
            'POST',
            '/api/webhooks/stripe',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $header,
            ],
            $json
        );
    }
}
