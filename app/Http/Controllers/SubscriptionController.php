<?php

namespace App\Http\Controllers;

use App\Models\BillingTransaction;
use App\Models\Coupon;
use App\Models\Subscription;
use App\Services\Payments\CheckoutPaymentMethodService;
use App\Services\Payments\PaymentFeature;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly PaymentFeature $paymentFeature,
        private readonly CheckoutPaymentMethodService $checkoutPaymentMethodService
    ) {
    }

    /**
     * Create a Stripe Checkout Session for a subscription.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|in:pro,enterprise',
            'interval' => 'required|in:monthly,annual',
            'coupon_code' => 'nullable|string',
        ]);

        $user = $request->user();
        $isAnnual = $validated['interval'] === 'annual';
        $currency = strtoupper((string) ($user->currency ?? 'BRL'));
        $paymentMethodTypes = $this->checkoutPaymentMethodService->resolveForSubscriptionCheckout($currency);

        // Define Price IDs (Should be in .env)
        $priceId = $isAnnual 
            ? env('STRIPE_PRO_ANNUAL_PRICE_ID') 
            : env('STRIPE_PRO_MONTHLY_PRICE_ID');

        $sessionOptions = [
            'payment_method_types' => $paymentMethodTypes,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => url('/dashboard?subscription=success'),
            'cancel_url' => url('/dashboard/account?cancel=1'),
            'customer_email' => $user->email,
            'client_reference_id' => $user->id,
            'metadata' => [
                'user_id' => $user->id,
                'plan' => $validated['plan'],
                'interval' => $validated['interval'],
                'account_mode' => $user->account_mode ?? 'scheduling_only',
                'currency' => $currency,
                'payment_method_types' => implode(',', $paymentMethodTypes),
            ],
        ];

        // Handle Coupons
        $coupon = null;
        if (!empty($validated['coupon_code'])) {
            $coupon = Coupon::whereRaw('LOWER(code) = ?', [strtolower($validated['coupon_code'])])->first();
            if (!$coupon || !$coupon->isValid()) {
                return response()->json(['message' => 'Invalid or expired coupon.'], 422);
            }

            // If 100% discount, we can bypass Stripe if explicitly free
            if ($coupon->discount_type === 'percent' && (float)$coupon->discount_value >= 100) {
                $coupon->increment('times_used');
                $user->update([
                    'subscription_tier' => $validated['plan'],
                    'billing_cycle' => $validated['interval'],
                    'trial_ends_at' => now()->addMonth(), // Give a month even if 100% off
                ]);

                Subscription::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'provider' => 'stripe',
                        'plan_code' => $validated['plan'],
                        'billing_cycle' => $validated['interval'],
                    ],
                    [
                        'account_mode' => $user->account_mode ?? 'scheduling_only',
                        'currency' => $user->currency ?? 'BRL',
                        'status' => 'active',
                        'price' => 0,
                        'started_at' => now(),
                        'current_period_start' => now(),
                        'current_period_end' => now()->addMonth(),
                        'metadata' => [
                            'coupon_code' => $coupon->code,
                            'feature_gate' => $this->paymentFeature->isEnabledForUser($user),
                        ],
                    ]
                );

                BillingTransaction::create([
                    'user_id' => $user->id,
                    'source' => 'subscription',
                    'status' => 'paid',
                    'amount' => 0,
                    'currency' => $currency,
                    'coupon_code' => $coupon->code,
                    'description' => "Assinatura {$validated['plan']} ({$validated['interval']}) via cupom 100%",
                    'metadata' => [
                        'plan' => $validated['plan'],
                        'interval' => $validated['interval'],
                        'coupon_code' => $coupon->code,
                        'payment_method_types' => $paymentMethodTypes,
                    ],
                    'paid_at' => now(),
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Sovereign Node Activated',
                    'redirect_url' => url('/dashboard?subscription=free_success')
                ]);
            }
        }

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_code' => $validated['plan'],
            'billing_cycle' => $validated['interval'],
            'account_mode' => $user->account_mode ?? 'scheduling_only',
            'provider' => 'stripe',
            'price' => 0,
            'currency' => $user->currency ?? 'BRL',
            'status' => 'pending',
            'metadata' => [
                'feature_gate' => $this->paymentFeature->isEnabledForUser($user),
            ],
        ]);

        $transaction = BillingTransaction::create([
            'user_id' => $user->id,
            'source' => 'subscription',
            'status' => 'pending',
            'amount' => null,
            'currency' => $currency,
            'coupon_code' => $coupon?->code,
            'description' => "Checkout assinatura {$validated['plan']} ({$validated['interval']})",
            'metadata' => [
                'plan' => $validated['plan'],
                'interval' => $validated['interval'],
                'coupon_code' => $coupon?->code,
                'payment_method_types' => $paymentMethodTypes,
            ],
        ]);

        $sessionOptions['metadata']['transaction_id'] = $transaction->id;
        $sessionOptions['metadata']['subscription_record_id'] = $subscription->id;
        $sessionOptions['metadata']['coupon_code'] = $coupon?->code;

        $session = $this->stripe()->checkout->sessions->create($sessionOptions);

        $transaction->update([
            'external_reference' => $session->id,
        ]);

        return response()->json([
            'checkout_url' => $session->url
        ]);
    }

    private function stripe(): StripeClient
    {
        $secret = (string) (config('payments.stripe.secret') ?: config('services.stripe.secret') ?: env('STRIPE_SECRET'));

        return new StripeClient($secret);
    }
}
