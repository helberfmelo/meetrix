<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Mail\BookingConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe Webhook events.
     */
    public function handle(Request $request)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $payload = $request->all();
        $type = $payload['type'] ?? '';

        if ($type === 'checkout.session.completed') {
            $session = $payload['data']['object'];
            $bookingId = $session['metadata']['booking_id'] ?? null;

            if ($bookingId) {
                $booking = Booking::with(['schedulingPage', 'appointmentType'])->find($bookingId);
                if ($booking) {
                    $booking->update([
                        'status' => 'confirmed',
                        'is_paid' => true,
                        'amount_paid' => $session['amount_total'] / 100, // Converts from cents
                    ]);
                    
                    // Send Email for Paid Booking
                    Mail::to($booking->customer_email)->send(new BookingConfirmation($booking));

                    Log::info("Booking {$bookingId} marked as confirmed via Stripe Webhook.");
                }
            }
        }

        if ($type === 'customer.subscription.created' || $type === 'customer.subscription.updated') {
            $subscription = $payload['data']['object'];
            $userId = $subscription['metadata']['user_id'] ?? null;

            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->update([
                        'subscription_tier' => 'Pro',
                        'stripe_id' => $subscription['customer'],
                        'subscription_ends_at' => Carbon::createFromTimestamp($subscription['current_period_end']),
                    ]);
                    Log::info("User {$userId} subscription updated via Webhook.");
                }
            }
        }

        if ($type === 'customer.subscription.deleted') {
            $subscription = $payload['data']['object'];
            $userId = $subscription['metadata']['user_id'] ?? null;

            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->update([
                        'subscription_tier' => 'Free',
                        'subscription_ends_at' => null,
                    ]);
                    Log::info("User {$userId} subscription cancelled via Webhook.");
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
