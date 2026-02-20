<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmation;
use App\Models\BillingTransaction;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe Webhook events.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $type = $payload['type'] ?? '';

        if ($type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($payload['data']['object'] ?? []);
        }

        if (in_array($type, ['checkout.session.expired', 'checkout.session.async_payment_failed'], true)) {
            $this->handleCheckoutFailure($payload['data']['object'] ?? []);
        }

        if (in_array($type, ['customer.subscription.created', 'customer.subscription.updated'], true)) {
            $this->handleSubscriptionUpdated($payload['data']['object'] ?? []);
        }

        if ($type === 'customer.subscription.deleted') {
            $this->handleSubscriptionDeleted($payload['data']['object'] ?? []);
        }

        return response()->json(['status' => 'success']);
    }

    private function handleCheckoutCompleted(array $session): void
    {
        $metadata = $session['metadata'] ?? [];
        $bookingId = $metadata['booking_id'] ?? null;
        $transactionId = $metadata['transaction_id'] ?? null;
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
                    Mail::mailer($mailer)->to($booking->customer_email)->send(new BookingConfirmation($booking));
                } catch (\Throwable $mailException) {
                    Log::error("Booking confirmation mail failed via webhook [mailer={$mailer}]: " . $mailException->getMessage());
                }
                Log::info("Booking {$bookingId} marked as confirmed via Stripe Webhook.");
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
                        'subscription_tier' => strtolower($metadata['plan'] ?? 'pro'),
                        'billing_cycle' => $metadata['interval'] ?? 'monthly',
                        'stripe_id' => $session['customer'] ?? $user->stripe_id,
                        'trial_ends_at' => null,
                    ]);
                }
            }
        }
    }

    private function handleCheckoutFailure(array $session): void
    {
        $transactionId = $session['metadata']['transaction_id'] ?? null;

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

    private function handleSubscriptionUpdated(array $subscription): void
    {
        $userId = $subscription['metadata']['user_id'] ?? null;
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

        $plan = strtolower($subscription['metadata']['plan'] ?? 'pro');

        $user->update([
            'subscription_tier' => $plan,
            'stripe_id' => $subscription['customer'] ?? $user->stripe_id,
            'subscription_ends_at' => !empty($subscription['current_period_end'])
                ? Carbon::createFromTimestamp($subscription['current_period_end'])
                : $user->subscription_ends_at,
        ]);

        Log::info("User {$user->id} subscription updated via Webhook.");
    }

    private function handleSubscriptionDeleted(array $subscription): void
    {
        $userId = $subscription['metadata']['user_id'] ?? null;
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

        Log::info("User {$user->id} subscription cancelled via Webhook.");
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
