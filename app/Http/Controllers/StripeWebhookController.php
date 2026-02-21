<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmation;
use App\Models\BillingTransaction;
use App\Models\Booking;
use App\Models\ConnectedAccount;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\PaymentIntent;
use App\Models\Subscription;
use App\Models\Transfer;
use App\Models\User;
use App\Models\WebhookEvent;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe Webhook events with signature validation and idempotency.
     */
    public function handle(Request $request)
    {
        $payload = $this->resolvePayload($request);
        if (!is_array($payload)) {
            return $payload;
        }

        $type = (string) ($payload['type'] ?? '');
        $eventId = (string) ($payload['id'] ?? '');

        $webhookEvent = null;
        if ($eventId !== '') {
            $webhookEvent = $this->registerWebhookEvent($eventId, $type, $payload);
            if (!$webhookEvent) {
                return response()->json(['status' => 'duplicated']);
            }
        }

        DB::beginTransaction();

        try {
            $object = (array) data_get($payload, 'data.object', []);

            switch ($type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutCompleted($object);
                    break;
                case 'checkout.session.expired':
                case 'checkout.session.async_payment_failed':
                    $this->handleCheckoutFailure($object);
                    break;
                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($object);
                    break;
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($object);
                    break;
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($object);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($object);
                    break;
                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($object);
                    break;
                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($object);
                    break;
                case 'charge.refunded':
                    $this->handleChargeRefunded($object);
                    break;
                default:
                    Log::info('Stripe webhook ignored event type.', ['type' => $type]);
                    break;
            }

            if ($webhookEvent) {
                $webhookEvent->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'payload' => $payload,
                ]);
            }

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Throwable $exception) {
            DB::rollBack();

            if ($eventId !== '') {
                WebhookEvent::updateOrCreate(
                    ['provider' => 'stripe', 'event_id' => $eventId],
                    [
                        'event_type' => $type,
                        'status' => 'failed',
                        'processed_at' => now(),
                        'payload' => $payload,
                    ]
                );
            }

            Log::error('Stripe webhook processing failed.', [
                'event_id' => $eventId,
                'type' => $type,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Webhook processing failed.'], 500);
        }
    }

    private function resolvePayload(Request $request): array|\Illuminate\Http\JsonResponse
    {
        $rawPayload = (string) $request->getContent();
        $payload = json_decode($rawPayload, true);

        if (!is_array($payload)) {
            return response()->json(['message' => 'Invalid JSON payload.'], 400);
        }

        $secret = (string) (config('payments.stripe.webhook_secret') ?: config('services.stripe.webhook_secret'));
        if ($secret === '') {
            return $payload;
        }

        $signatureHeader = (string) $request->header('Stripe-Signature', '');
        if ($signatureHeader === '') {
            return response()->json(['message' => 'Missing Stripe signature header.'], 400);
        }

        try {
            Webhook::constructEvent($rawPayload, $signatureHeader, $secret);
        } catch (SignatureVerificationException|\UnexpectedValueException $exception) {
            Log::warning('Stripe webhook signature validation failed.', [
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        return $payload;
    }

    private function registerWebhookEvent(string $eventId, string $eventType, array $payload): ?WebhookEvent
    {
        try {
            return WebhookEvent::create([
                'provider' => 'stripe',
                'event_id' => $eventId,
                'event_type' => $eventType,
                'status' => 'processing',
                'payload' => $payload,
            ]);
        } catch (QueryException $exception) {
            if ($this->isUniqueViolation($exception)) {
                return null;
            }

            throw $exception;
        }
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');

        return in_array($sqlState, ['23000', '23505'], true);
    }

    private function handleCheckoutCompleted(array $session): void
    {
        $metadata = (array) ($session['metadata'] ?? []);
        $bookingId = isset($metadata['booking_id']) ? (int) $metadata['booking_id'] : null;
        $transactionId = isset($metadata['transaction_id']) ? (int) $metadata['transaction_id'] : null;
        $amountTotal = isset($session['amount_total']) ? ((float) $session['amount_total']) / 100 : null;

        if ($bookingId) {
            $booking = Booking::with(['schedulingPage', 'appointmentType'])->find($bookingId);

            if ($booking) {
                $booking->update([
                    'status' => 'confirmed',
                    'is_paid' => true,
                    'amount_paid' => $amountTotal,
                ]);

                $mailer = $this->resolveTransactionalMailer();

                try {
                    $sentMessage = Mail::mailer($mailer)->to($booking->customer_email)->send(new BookingConfirmation($booking));
                    $messageId = $sentMessage?->getMessageId();

                    Log::info('Booking confirmation mail sent via webhook.', [
                        'booking_id' => $booking->id,
                        'recipient' => $booking->customer_email,
                        'mailer' => $mailer,
                        'message_id' => $messageId,
                    ]);
                } catch (\Throwable $mailException) {
                    Log::error("Booking confirmation mail failed via webhook [mailer={$mailer}]: " . $mailException->getMessage());
                }
            }
        }

        if ($transactionId) {
            $transaction = BillingTransaction::find($transactionId);
            if ($transaction) {
                $wasPaid = $transaction->status === 'paid';

                $transaction->update([
                    'status' => 'paid',
                    'amount' => $amountTotal ?? $transaction->amount,
                    'external_reference' => $session['id'] ?? $transaction->external_reference,
                    'paid_at' => now(),
                ]);

                if (!$wasPaid && !empty($transaction->coupon_code)) {
                    Coupon::whereRaw('LOWER(code) = ?', [strtolower($transaction->coupon_code)])
                        ->increment('times_used');
                }
            }
        }

        if (($session['mode'] ?? null) === 'subscription') {
            $userId = $metadata['user_id'] ?? ($session['client_reference_id'] ?? null);
            if ($userId) {
                $user = User::find($userId);

                if ($user) {
                    $user->update([
                        'subscription_tier' => strtolower((string) ($metadata['plan'] ?? 'pro')),
                        'billing_cycle' => (string) ($metadata['interval'] ?? 'monthly'),
                        'stripe_id' => (string) ($session['customer'] ?? $user->stripe_id),
                        'trial_ends_at' => null,
                    ]);
                }
            }
        }
    }

    private function handleCheckoutFailure(array $session): void
    {
        $transactionId = isset($session['metadata']['transaction_id']) ? (int) $session['metadata']['transaction_id'] : null;

        if (!$transactionId) {
            return;
        }

        $transaction = BillingTransaction::find($transactionId);
        if ($transaction && $transaction->status !== 'paid') {
            $transaction->update([
                'status' => 'failed',
                'external_reference' => $session['id'] ?? $transaction->external_reference,
            ]);
        }
    }

    private function handlePaymentIntentSucceeded(array $intent): void
    {
        $intentId = (string) ($intent['id'] ?? '');
        if ($intentId === '') {
            return;
        }

        $metadata = (array) ($intent['metadata'] ?? []);
        $bookingId = isset($metadata['booking_id']) ? (int) $metadata['booking_id'] : null;
        $transactionId = isset($metadata['transaction_id']) ? (int) $metadata['transaction_id'] : null;
        $userId = $this->resolveUserIdFromMetadata($metadata, $bookingId);
        $idempotencyKey = trim((string) ($metadata['idempotency_key'] ?? ''));
        $amount = ((float) ($intent['amount_received'] ?? $intent['amount'] ?? 0)) / 100;
        $currency = strtoupper((string) ($intent['currency'] ?? 'BRL'));

        if ($userId) {
            PaymentIntent::updateOrCreate(
                ['provider_intent_id' => $intentId],
                [
                    'user_id' => $userId,
                    'booking_id' => $bookingId,
                    'provider' => 'stripe',
                    'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : null,
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => (string) ($intent['status'] ?? 'succeeded'),
                    'payload' => $intent,
                    'confirmed_at' => now(),
                    'last_error_code' => null,
                    'last_error_message' => null,
                ]
            );
        }

        if ($bookingId) {
            $booking = Booking::find($bookingId);
            if ($booking) {
                $booking->update([
                    'status' => 'confirmed',
                    'is_paid' => true,
                    'amount_paid' => $amount,
                ]);
            }
        }

        if ($transactionId) {
            $transaction = BillingTransaction::find($transactionId);
            if ($transaction) {
                $wasPaid = $transaction->status === 'paid';

                $transaction->update([
                    'status' => 'paid',
                    'amount' => $amount > 0 ? $amount : $transaction->amount,
                    'currency' => $currency,
                    'external_reference' => (string) ($intent['latest_charge'] ?? $intentId),
                    'paid_at' => now(),
                ]);

                if (!$wasPaid && !empty($transaction->coupon_code)) {
                    Coupon::whereRaw('LOWER(code) = ?', [strtolower($transaction->coupon_code)])
                        ->increment('times_used');
                }
            }
        }

        if (!$userId) {
            return;
        }

        $platformFeePercent = (float) ($metadata['platform_fee_percent'] ?? 0);
        $platformFeeAmount = isset($metadata['platform_fee_amount_cents'])
            ? ((float) $metadata['platform_fee_amount_cents']) / 100
            : round($amount * ($platformFeePercent / 100), 2);
        $netAmount = isset($metadata['net_amount_cents'])
            ? ((float) $metadata['net_amount_cents']) / 100
            : max(0, $amount - $platformFeeAmount);

        $chargeId = (string) ($intent['latest_charge'] ?? '');
        $lookup = $chargeId !== ''
            ? ['provider_payment_id' => $chargeId]
            : ['provider_intent_id' => $intentId];

        $payment = Payment::updateOrCreate(
            $lookup,
            [
                'user_id' => $userId,
                'booking_id' => $bookingId,
                'provider' => 'stripe',
                'provider_intent_id' => $intentId,
                'amount' => $amount,
                'currency' => $currency,
                'platform_fee_percent' => $platformFeePercent,
                'platform_fee_amount' => $platformFeeAmount,
                'net_amount' => $netAmount,
                'status' => 'paid',
                'paid_at' => now(),
                'metadata' => $intent,
            ]
        );

        $connectedAccountId = isset($metadata['connected_account_id']) ? (int) $metadata['connected_account_id'] : null;
        if ($connectedAccountId && $payment) {
            $connectedAccount = ConnectedAccount::find($connectedAccountId);
            if (!$connectedAccount) {
                Log::warning('Connected account metadata not found for transfer record.', [
                    'connected_account_id' => $connectedAccountId,
                    'payment_id' => $payment->id,
                    'intent_id' => $intentId,
                ]);

                return;
            }

            Transfer::updateOrCreate(
                [
                    'payment_id' => $payment->id,
                    'connected_account_id' => $connectedAccount->id,
                ],
                [
                    'user_id' => $userId,
                    'provider' => 'stripe',
                    'amount' => max(0, $netAmount),
                    'currency' => $currency,
                    'status' => 'succeeded',
                    'transferred_at' => now(),
                    'metadata' => [
                        'destination' => $metadata['connected_account_ref'] ?? null,
                        'payment_intent_id' => $intentId,
                    ],
                ]
            );
        }
    }

    private function handlePaymentIntentFailed(array $intent): void
    {
        $intentId = (string) ($intent['id'] ?? '');
        if ($intentId === '') {
            return;
        }

        $metadata = (array) ($intent['metadata'] ?? []);
        $bookingId = isset($metadata['booking_id']) ? (int) $metadata['booking_id'] : null;
        $transactionId = isset($metadata['transaction_id']) ? (int) $metadata['transaction_id'] : null;
        $userId = $this->resolveUserIdFromMetadata($metadata, $bookingId);
        $idempotencyKey = trim((string) ($metadata['idempotency_key'] ?? ''));
        $amount = ((float) ($intent['amount'] ?? 0)) / 100;
        $currency = strtoupper((string) ($intent['currency'] ?? 'BRL'));
        $errorCode = (string) data_get($intent, 'last_payment_error.code', '');
        $errorMessage = (string) data_get($intent, 'last_payment_error.message', '');

        if ($userId) {
            PaymentIntent::updateOrCreate(
                ['provider_intent_id' => $intentId],
                [
                    'user_id' => $userId,
                    'booking_id' => $bookingId,
                    'provider' => 'stripe',
                    'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : null,
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => (string) ($intent['status'] ?? 'failed'),
                    'payload' => $intent,
                    'last_error_code' => $errorCode !== '' ? $errorCode : null,
                    'last_error_message' => $errorMessage !== '' ? $errorMessage : null,
                    'confirmed_at' => null,
                ]
            );
        }

        if ($transactionId) {
            BillingTransaction::whereKey($transactionId)->update([
                'status' => 'failed',
                'external_reference' => $intentId,
            ]);
        }

        if (!$userId) {
            return;
        }

        Payment::updateOrCreate(
            ['provider_intent_id' => $intentId],
            [
                'user_id' => $userId,
                'booking_id' => $bookingId,
                'provider' => 'stripe',
                'amount' => $amount,
                'currency' => $currency,
                'platform_fee_percent' => (float) ($metadata['platform_fee_percent'] ?? 0),
                'platform_fee_amount' => 0,
                'net_amount' => 0,
                'status' => 'failed',
                'failed_at' => now(),
                'metadata' => $intent,
            ]
        );
    }

    private function handleInvoicePaymentSucceeded(array $invoice): void
    {
        $metadata = (array) ($invoice['metadata'] ?? []);
        $user = $this->resolveUserFromInvoice($invoice, $metadata);
        if (!$user) {
            return;
        }

        $plan = strtolower((string) ($metadata['plan'] ?? $user->subscription_tier ?? 'pro'));
        $interval = (string) ($metadata['interval'] ?? $user->billing_cycle ?? 'monthly');
        $subscriptionProviderId = (string) ($invoice['subscription'] ?? '');
        $subscriptionRecordId = isset($metadata['subscription_record_id']) ? (int) $metadata['subscription_record_id'] : null;
        $amount = ((float) ($invoice['amount_paid'] ?? $invoice['amount_due'] ?? 0)) / 100;
        $currency = strtoupper((string) ($invoice['currency'] ?? $user->currency ?? 'BRL'));

        $periodStart = data_get($invoice, 'lines.data.0.period.start');
        $periodEnd = data_get($invoice, 'lines.data.0.period.end');

        $subscription = null;
        if ($subscriptionRecordId) {
            $subscription = Subscription::query()
                ->where('id', $subscriptionRecordId)
                ->where('user_id', $user->id)
                ->first();
        }

        if (!$subscription && $subscriptionProviderId !== '') {
            $subscription = Subscription::query()
                ->where('provider', 'stripe')
                ->where('provider_subscription_id', $subscriptionProviderId)
                ->first();
        }

        if (!$subscription) {
            $subscription = new Subscription([
                'user_id' => $user->id,
                'provider' => 'stripe',
            ]);
        }

        $subscription->fill([
            'plan_code' => $plan,
            'billing_cycle' => $interval,
            'account_mode' => $metadata['account_mode'] ?? ($user->account_mode ?? 'scheduling_only'),
            'provider_subscription_id' => $subscriptionProviderId !== '' ? $subscriptionProviderId : $subscription->provider_subscription_id,
            'price' => $amount,
            'currency' => $currency,
            'status' => 'active',
            'started_at' => $subscription->started_at ?? now(),
            'current_period_start' => is_numeric($periodStart) ? Carbon::createFromTimestamp((int) $periodStart) : $subscription->current_period_start,
            'current_period_end' => is_numeric($periodEnd) ? Carbon::createFromTimestamp((int) $periodEnd) : $subscription->current_period_end,
            'canceled_at' => null,
            'metadata' => $invoice,
        ]);
        $subscription->save();

        $user->update([
            'subscription_tier' => $plan,
            'billing_cycle' => $interval,
            'stripe_id' => (string) ($invoice['customer'] ?? $user->stripe_id),
            'subscription_ends_at' => $subscription->current_period_end,
        ]);

        Payment::updateOrCreate(
            ['provider_payment_id' => (string) ($invoice['id'] ?? '')],
            [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'provider' => 'stripe',
                'provider_intent_id' => (string) ($invoice['payment_intent'] ?? null),
                'amount' => $amount,
                'currency' => $currency,
                'platform_fee_percent' => 0,
                'platform_fee_amount' => 0,
                'net_amount' => $amount,
                'status' => 'paid',
                'paid_at' => now(),
                'metadata' => $invoice,
            ]
        );

        $transactionId = isset($metadata['transaction_id']) ? (int) $metadata['transaction_id'] : null;
        if ($transactionId) {
            BillingTransaction::whereKey($transactionId)->update([
                'status' => 'paid',
                'amount' => $amount,
                'currency' => $currency,
                'external_reference' => (string) ($invoice['id'] ?? ''),
                'paid_at' => now(),
            ]);
        }
    }

    private function handleInvoicePaymentFailed(array $invoice): void
    {
        $metadata = (array) ($invoice['metadata'] ?? []);
        $user = $this->resolveUserFromInvoice($invoice, $metadata);
        if (!$user) {
            return;
        }

        $subscriptionProviderId = (string) ($invoice['subscription'] ?? '');
        if ($subscriptionProviderId !== '') {
            Subscription::query()
                ->where('provider', 'stripe')
                ->where('provider_subscription_id', $subscriptionProviderId)
                ->update([
                    'status' => 'past_due',
                    'metadata' => $invoice,
                ]);
        }

        $amount = ((float) ($invoice['amount_due'] ?? 0)) / 100;
        $currency = strtoupper((string) ($invoice['currency'] ?? $user->currency ?? 'BRL'));

        Payment::updateOrCreate(
            ['provider_payment_id' => (string) ($invoice['id'] ?? '')],
            [
                'user_id' => $user->id,
                'provider' => 'stripe',
                'provider_intent_id' => (string) ($invoice['payment_intent'] ?? null),
                'amount' => $amount,
                'currency' => $currency,
                'platform_fee_percent' => 0,
                'platform_fee_amount' => 0,
                'net_amount' => 0,
                'status' => 'failed',
                'failed_at' => now(),
                'metadata' => $invoice,
            ]
        );

        $transactionId = isset($metadata['transaction_id']) ? (int) $metadata['transaction_id'] : null;
        if ($transactionId) {
            BillingTransaction::whereKey($transactionId)->update([
                'status' => 'failed',
                'external_reference' => (string) ($invoice['id'] ?? ''),
            ]);
        }
    }

    private function handleChargeRefunded(array $charge): void
    {
        $chargeId = (string) ($charge['id'] ?? '');
        $intentId = (string) ($charge['payment_intent'] ?? '');
        $metadata = (array) ($charge['metadata'] ?? []);
        $transactionId = isset($metadata['transaction_id']) ? (int) $metadata['transaction_id'] : null;
        $amount = ((float) ($charge['amount_refunded'] ?? $charge['amount'] ?? 0)) / 100;
        $currency = strtoupper((string) ($charge['currency'] ?? 'BRL'));

        $payment = null;
        if ($chargeId !== '') {
            $payment = Payment::query()->where('provider_payment_id', $chargeId)->first();
        }

        if (!$payment && $intentId !== '') {
            $payment = Payment::query()->where('provider_intent_id', $intentId)->first();
        }

        if ($payment) {
            $payment->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'metadata' => $charge,
            ]);
        }

        if ($intentId !== '') {
            PaymentIntent::query()
                ->where('provider_intent_id', $intentId)
                ->update([
                    'status' => 'refunded',
                    'payload' => $charge,
                ]);
        }

        $transaction = null;
        if ($transactionId) {
            $transaction = BillingTransaction::find($transactionId);
        }

        if (!$transaction) {
            $transaction = BillingTransaction::query()
                ->whereIn('external_reference', array_filter([$chargeId, $intentId]))
                ->latest()
                ->first();
        }

        if ($transaction) {
            $transaction->update([
                'status' => 'refunded',
                'amount' => $amount > 0 ? $amount : $transaction->amount,
                'currency' => $currency,
                'external_reference' => $chargeId !== '' ? $chargeId : $transaction->external_reference,
            ]);
        }
    }

    private function handleSubscriptionUpdated(array $subscription): void
    {
        $metadata = (array) ($subscription['metadata'] ?? []);
        $userId = $metadata['user_id'] ?? null;
        $user = null;

        if ($userId) {
            $user = User::find($userId);
        }

        if (!$user && !empty($subscription['customer'])) {
            $user = User::where('stripe_id', $subscription['customer'])->first();
        }

        if (!$user) {
            return;
        }

        $plan = strtolower((string) ($metadata['plan'] ?? 'pro'));

        $user->update([
            'subscription_tier' => $plan,
            'stripe_id' => (string) ($subscription['customer'] ?? $user->stripe_id),
            'subscription_ends_at' => !empty($subscription['current_period_end'])
                ? Carbon::createFromTimestamp((int) $subscription['current_period_end'])
                : $user->subscription_ends_at,
        ]);
    }

    private function handleSubscriptionDeleted(array $subscription): void
    {
        $metadata = (array) ($subscription['metadata'] ?? []);
        $userId = $metadata['user_id'] ?? null;
        $user = null;

        if ($userId) {
            $user = User::find($userId);
        }

        if (!$user && !empty($subscription['customer'])) {
            $user = User::where('stripe_id', $subscription['customer'])->first();
        }

        if (!$user) {
            return;
        }

        $user->update([
            'subscription_tier' => 'free',
            'subscription_ends_at' => null,
        ]);
    }

    private function resolveUserIdFromMetadata(array $metadata, ?int $bookingId = null): ?int
    {
        if (!empty($metadata['user_id']) && is_numeric($metadata['user_id'])) {
            return (int) $metadata['user_id'];
        }

        if ($bookingId) {
            $booking = Booking::with('schedulingPage:id,user_id')->find($bookingId);
            if ($booking?->schedulingPage?->user_id) {
                return (int) $booking->schedulingPage->user_id;
            }
        }

        return null;
    }

    private function resolveUserFromInvoice(array $invoice, array $metadata): ?User
    {
        $userId = isset($metadata['user_id']) && is_numeric($metadata['user_id'])
            ? (int) $metadata['user_id']
            : null;

        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                return $user;
            }
        }

        $customerId = (string) ($invoice['customer'] ?? '');
        if ($customerId !== '') {
            return User::where('stripe_id', $customerId)->first();
        }

        return null;
    }

    private function resolveTransactionalMailer(): string
    {
        $smtpConfig = (array) config('mail.mailers.smtp', []);
        $smtpHost = (string) ($smtpConfig['host'] ?? '');
        $smtpUser = (string) ($smtpConfig['username'] ?? '');
        $smtpPassword = (string) ($smtpConfig['password'] ?? '');

        if (
            $smtpHost !== ''
            && !in_array($smtpHost, ['127.0.0.1', 'localhost'], true)
            && $smtpUser !== ''
            && $smtpPassword !== ''
        ) {
            return 'smtp';
        }

        return (string) config('mail.default', 'log');
    }
}
