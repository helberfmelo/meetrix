<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmation;
use App\Models\AppointmentType;
use App\Models\BillingTransaction;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\SchedulingPage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class BookingController extends Controller
{
    /**
     * Store a newly created booking.
     */
    public function store(Request $request)
    {
        $baseValidation = $request->validate([
            'scheduling_page_id' => 'required|exists:scheduling_pages,id',
            'appointment_type_id' => 'required|exists:appointment_types,id',
            'start_at' => 'required|date',
            'timezone' => 'nullable|string',
            'coupon_code' => 'nullable|string',
        ]);

        $page = SchedulingPage::query()
            ->where('id', $baseValidation['scheduling_page_id'])
            ->where('is_active', true)
            ->first();

        if (!$page) {
            return response()->json(['message' => 'Scheduling page is inactive or unavailable.'], 422);
        }

        $type = AppointmentType::query()
            ->where('id', $baseValidation['appointment_type_id'])
            ->where('scheduling_page_id', $page->id)
            ->where('is_active', true)
            ->first();

        if (!$type) {
            return response()->json(['message' => 'Selected service is not available for this page.'], 422);
        }

        $fields = $page->form_fields;
        if (!is_array($fields) || empty($fields)) {
            $fields = [
                ['name' => 'customer_name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
                ['name' => 'customer_email', 'label' => 'Email Address', 'type' => 'email', 'required' => true],
            ];
        }

        $validationRules = [];

        foreach ($fields as $field) {
            if (!is_array($field) || empty($field['name'])) {
                continue;
            }

            $rules = [];
            if ($field['required'] ?? false) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }

            if (($field['type'] ?? null) === 'email') {
                $rules[] = 'email';
            }

            if (in_array(($field['type'] ?? null), ['text', 'textarea'], true)) {
                $rules[] = 'string';
            }

            $validationRules[$field['name']] = implode('|', $rules);
        }

        $validatedDynamicFields = $request->validate($validationRules);

        $customerTimezone = $baseValidation['timezone'] ?? 'UTC';
        $startTime = Carbon::parse($baseValidation['start_at'], $customerTimezone)->utc();

        if ($startTime->lte(now()->utc())) {
            return response()->json(['message' => 'Selected start time must be in the future.'], 422);
        }

        $endTime = $startTime->copy()->addMinutes($type->duration_minutes);

        $conflicts = Booking::query()
            ->where('scheduling_page_id', $page->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_at', '<', $endTime)
                    ->where('end_at', '>', $startTime);
            })
            ->exists();

        if ($conflicts) {
            return response()->json(['message' => 'This time slot has just been taken.'], 409);
        }

        $customerName = $validatedDynamicFields['customer_name'] ?? $request->input('customer_name');
        $customerEmail = $validatedDynamicFields['customer_email'] ?? $request->input('customer_email');
        $customerPhone = $validatedDynamicFields['phone'] ?? $request->input('phone');

        if (empty($customerName) || empty($customerEmail)) {
            return response()->json(['message' => 'Customer name and email are required.'], 422);
        }

        $customerData = [];
        foreach ($fields as $field) {
            if (!is_array($field) || empty($field['name'])) {
                continue;
            }

            if (!in_array($field['name'], ['customer_name', 'customer_email', 'phone'], true)) {
                $customerData[$field['name']] = $validatedDynamicFields[$field['name']] ?? null;
            }
        }

        $finalPrice = (float) $type->price;
        $coupon = null;

        if (!empty($baseValidation['coupon_code'])) {
            $coupon = Coupon::whereRaw('LOWER(code) = ?', [strtolower($baseValidation['coupon_code'])])->first();

            if (!$coupon || !$coupon->isValid()) {
                return response()->json(['message' => 'Invalid or expired coupon.'], 422);
            }

            if ($coupon->discount_type === 'percent') {
                $finalPrice -= ($finalPrice * ($coupon->discount_value / 100));
            } else {
                $finalPrice -= $coupon->discount_value;
            }

            $finalPrice = max(0, $finalPrice);
        }

        DB::beginTransaction();

        try {
            $booking = Booking::create([
                'scheduling_page_id' => $page->id,
                'appointment_type_id' => $type->id,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'customer_timezone' => $customerTimezone,
                'customer_data' => $customerData,
                'start_at' => $startTime,
                'end_at' => $endTime,
                'status' => $finalPrice > 0 ? 'pending' : 'confirmed',
            ]);

            if ($finalPrice > 0) {
                Stripe::setApiKey(env('STRIPE_SECRET'));

                $transaction = BillingTransaction::create([
                    'user_id' => $page->user_id,
                    'booking_id' => $booking->id,
                    'source' => 'booking',
                    'status' => 'pending',
                    'amount' => $finalPrice,
                    'currency' => $type->currency ?? 'BRL',
                    'coupon_code' => $coupon?->code,
                    'description' => "Pagamento de agendamento: {$type->name}",
                    'metadata' => [
                        'scheduling_page_id' => $page->id,
                        'appointment_type_id' => $type->id,
                        'start_at' => $startTime->toIso8601String(),
                    ],
                ]);

                $session = Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => strtolower($type->currency ?? 'BRL'),
                            'product_data' => [
                                'name' => "Booking: {$type->name}",
                                'description' => 'Scheduled for ' . $startTime->setTimezone($customerTimezone)->format('d/m/Y H:i'),
                            ],
                            'unit_amount' => (int) round($finalPrice * 100),
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => url("/p/{$page->slug}?success=1&booking={$booking->id}"),
                    'cancel_url' => url("/p/{$page->slug}?cancel=1"),
                    'customer_email' => $customerEmail,
                    'metadata' => [
                        'booking_id' => (string) $booking->id,
                        'transaction_id' => (string) $transaction->id,
                        'coupon_code' => $coupon?->code,
                        'source' => 'booking',
                    ],
                ]);

                $booking->update(['stripe_session_id' => $session->id]);
                $transaction->update(['external_reference' => $session->id]);

                DB::commit();

                return response()->json([
                    'requires_payment' => true,
                    'checkout_url' => $session->url,
                ], 201);
            }

            $booking->update([
                'status' => 'confirmed',
                'is_paid' => true,
                'amount_paid' => $finalPrice,
            ]);

            BillingTransaction::create([
                'user_id' => $page->user_id,
                'booking_id' => $booking->id,
                'source' => 'booking',
                'status' => 'paid',
                'amount' => $finalPrice,
                'currency' => $type->currency ?? 'BRL',
                'coupon_code' => $coupon?->code,
                'description' => "Agendamento confirmado sem cobranca: {$type->name}",
                'metadata' => [
                    'scheduling_page_id' => $page->id,
                    'appointment_type_id' => $type->id,
                ],
                'paid_at' => now(),
            ]);

            if ($coupon) {
                $coupon->increment('times_used');
            }

            DB::commit();

            $mailer = $this->resolveTransactionalMailer();

            try {
                Mail::mailer($mailer)->to($booking->customer_email)->send(new BookingConfirmation($booking));
            } catch (\Throwable $mailException) {
                // Booking is already committed. Mail failures should not invalidate the booking.
                Log::error("Booking confirmation mail failed [mailer={$mailer}]: " . $mailException->getMessage());
            }

            if ($page->config['whatsapp_enabled'] ?? false) {
                try {
                    $service = new \App\Services\WhatsAppService();
                    $message = "Ola {$booking->customer_name}, seu agendamento para {$type->name} em " . $startTime->setTimezone($customerTimezone)->format('d/m/Y \\a\\s H:i') . ' foi confirmado! Meetrix.pro';
                    $service->sendReminder($customerPhone ?? $booking->customer_email, $message);
                } catch (\Exception $e) {
                    Log::error('WhatsApp reminder failed: ' . $e->getMessage());
                }
            }

            try {
                (new \App\Services\Calendar\CalendarSyncService())->syncBooking($booking);
            } catch (\Exception $e) {
                Log::error('Calendar sync failed: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Booking confirmed!',
                'booking' => $booking,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Booking creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => 'Booking failed. Please try again.',
                'error_code' => 'booking_creation_failed',
            ], 500);
        }
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
