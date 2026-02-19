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
                // If 100% discount, we apply a Stripe coupon or a 100% discount trail
                // For simplicity, we apply a Stripe Coupon ID linked to the code if exists
                // Otherwise, we use a 100% discount trail (Stripe internally)
                $sessionOptions['discounts'] = [[
                    'coupon' => $coupon->code, // Assumes Stripe Coupon exists with same ID
                ]];
            }
        }

        $session = Session::create($sessionOptions);

        return response()->json([
            'checkout_url' => $session->url
        ]);
    }
}
