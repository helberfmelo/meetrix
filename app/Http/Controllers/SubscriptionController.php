<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Coupon;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    /**
     * Create a Stripe Checkout Session for a subscription.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:pro,enterprise',
            'interval' => 'required|in:monthly,annual',
            'coupon_code' => 'nullable|string',
        ]);

        $user = $request->user();
        $isAnnual = $request->interval === 'annual';
        
        // Define Price IDs (Should be in .env)
        $priceId = $isAnnual 
            ? env('STRIPE_PRO_ANNUAL_PRICE_ID') 
            : env('STRIPE_PRO_MONTHLY_PRICE_ID');

        $sessionOptions = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => url('/dashboard?subscription=success'),
            'cancel_url' => url('/dashboard/billing?cancel=1'),
            'customer_email' => $user->email,
            'client_reference_id' => $user->id,
            'metadata' => [
                'user_id' => $user->id,
                'plan' => $request->plan,
                'interval' => $request->interval,
            ],
        ];

        // Handle Coupons
        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', $request->coupon_code)->first();
            if ($coupon && $coupon->isValid()) {
                // If 100% discount, we can bypass Stripe if explicitly free
                if ($coupon->discount_type === 'percent' && (float)$coupon->discount_value >= 100) {
                    $user->update([
                        'subscription_tier' => $request->plan,
                        'trial_ends_at' => now()->addMonth(), // Give a month even if 100% off
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Sovereign Node Activated',
                        'redirect_url' => url('/dashboard?subscription=free_success')
                    ]);
                }

                $sessionOptions['discounts'] = [[
                    'coupon' => $coupon->code,
                ]];
            }
        }

        $session = Session::create($sessionOptions);

        return response()->json([
            'checkout_url' => $session->url
        ]);
    }
}
