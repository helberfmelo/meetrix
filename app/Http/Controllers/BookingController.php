<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\SchedulingPage;
use App\Models\AppointmentType;
use App\Mail\BookingConfirmation;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Store a newly created booking.
     */
    public function store(Request $request)
    {
        $page = SchedulingPage::findOrFail($request->scheduling_page_id);
        
        // Dynamic Validation
        $validationRules = [
            'scheduling_page_id' => 'required|exists:scheduling_pages,id',
            'appointment_type_id' => 'required|exists:appointment_types,id',
            'start_at' => 'required|date|after:now',
            'timezone' => 'nullable|string',
        ];

        $fields = $page->form_fields;
        $customerData = [];

        foreach ($fields as $field) {
            $rules = [];
            if ($field['required'] ?? false) $rules[] = 'required';
            else $rules[] = 'nullable';

            if ($field['type'] === 'email') $rules[] = 'email';
            if ($field['type'] === 'text') $rules[] = 'string';
            
            $validationRules[$field['name']] = implode('|', $rules);
        }

        $validated = $request->validate($validationRules);

        // Core fields are extracted from validated data based on field names
        $customerName = $validated['customer_name'] ?? $request->customer_name;
        $customerEmail = $validated['customer_email'] ?? $request->customer_email;

        // Store other fields in customer_data
        foreach ($fields as $field) {
            if (!in_array($field['name'], ['customer_name', 'customer_email'])) {
                $customerData[$field['name']] = $validated[$field['name']] ?? null;
            }
        }

        $type = AppointmentType::findOrFail($request->appointment_type_id);

        $startTime = Carbon::parse($request->start_at);
        $endTime = $startTime->copy()->addMinutes($type->duration_minutes);

        // Conflict check
        $conflicts = Booking::where('scheduling_page_id', $page->id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_at', '>=', $startTime)
                      ->where('start_at', '<', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->where('end_at', '>', $startTime)
                      ->where('end_at', '<=', $endTime);
                });
            })
            ->whereIn('status', ['confirmed', 'pending'])
            ->exists();

        if ($conflicts) {
            return response()->json(['message' => 'This time slot has just been taken.'], 409);
        }

        $booking = Booking::create([
            'scheduling_page_id' => $page->id,
            'appointment_type_id' => $type->id,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_data' => $customerData,
            'start_at' => $startTime,
            'end_at' => $endTime,
            'status' => $type->price > 0 ? 'pending' : 'confirmed',
            'customer_timezone' => $request->timezone ?? 'UTC',
        ]);

        // Handle Coupons
        $finalPrice = $type->price;
        if ($request->filled('coupon_code')) {
            $coupon = \App\Models\Coupon::where('code', $request->coupon_code)->first();
            if ($coupon && $coupon->isValid()) {
                if ($coupon->discount_type === 'percent') {
                    $finalPrice -= ($finalPrice * ($coupon->discount_value / 100));
                } else {
                    $finalPrice -= $coupon->discount_value;
                }
                $finalPrice = max(0, $finalPrice);
                $coupon->increment('times_used');
            }
        }

        // Handle Paid Bookings
        if ($finalPrice > 0) {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'brl',
                        'product_data' => [
                            'name' => "Booking: {$type->name}",
                            'description' => "Scheduled for " . $startTime->format('d/m/Y H:i'),
                        ],
                        'unit_amount' => $finalPrice * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url("/p/{$page->slug}?success=1&booking={$booking->id}"),
                'cancel_url' => url("/p/{$page->slug}?cancel=1"),
                'customer_email' => $request->customer_email,
                'metadata' => [
                    'booking_id' => $booking->id,
                ],
            ]);

            $booking->update(['stripe_session_id' => $session->id]);

            return response()->json([
                'requires_payment' => true,
                'checkout_url' => $session->url
            ], 201);
        }

        // 100% Discount or Free Booking - Instant Confirmation
        $booking->update(['status' => 'confirmed']);

        // Send Email for Free Booking
        Mail::to($booking->customer_email)->send(new BookingConfirmation($booking));

        // WhatsApp Pro Reminder
        if ($page->config['whatsapp_enabled'] ?? false) {
            try {
                $service = new \App\Services\WhatsAppService();
                $message = "Olá {$booking->customer_name}, seu agendamento para {$type->name} em " . $startTime->format('d/m/Y \à\s H:i') . " foi confirmado! Meetrix.pro";
                $service->sendReminder($booking->customer_data['phone'] ?? $booking->customer_email, $message);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("WhatsApp reminder failed: " . $e->getMessage());
            }
        }

        // Sync to External Calendars
        try {
            (new \App\Services\Calendar\CalendarSyncService())->syncBooking($booking);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Calendar sync failed: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Booking confirmed!',
            'booking' => $booking
        ], 201);
    }

    /**
     * List bookings for the authenticated user's pages.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $bookings = Booking::whereHas('schedulingPage', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['schedulingPage', 'appointmentType'])
        ->orderBy('start_at', 'desc')
        ->paginate(20);

        return response()->json($bookings);
    }
}
