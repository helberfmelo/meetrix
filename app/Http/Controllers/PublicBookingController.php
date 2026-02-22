<?php

namespace App\Http\Controllers;

use App\Mail\BookingCancelled;
use App\Mail\BookingRescheduled;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PublicBookingController extends Controller
{
    public function show(Request $request, string $slug, string $token)
    {
        $booking = Booking::query()
            ->with(['appointmentType', 'schedulingPage'])
            ->where('public_token', $token)
            ->whereHas('schedulingPage', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'start_at' => optional($booking->start_at)->toIso8601String(),
                'end_at' => optional($booking->end_at)->toIso8601String(),
                'customer_name' => $booking->customer_name,
                'customer_email' => $booking->customer_email,
                'customer_timezone' => $booking->customer_timezone,
            ],
            'appointment_type' => [
                'id' => $booking->appointmentType?->id,
                'name' => $booking->appointmentType?->name,
                'duration_minutes' => $booking->appointmentType?->duration_minutes,
                'currency' => $booking->appointmentType?->currency,
            ],
            'page' => [
                'id' => $booking->schedulingPage?->id,
                'title' => $booking->schedulingPage?->title,
                'slug' => $booking->schedulingPage?->slug,
                'config' => $booking->schedulingPage?->config,
            ],
        ]);
    }

    public function cancel(Request $request, string $slug, string $token)
    {
        $booking = Booking::query()
            ->with('schedulingPage')
            ->where('public_token', $token)
            ->whereHas('schedulingPage', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if (in_array($booking->status, ['cancelled', 'canceled'], true)) {
            return response()->json(['message' => 'Booking already cancelled.'], 409);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => 'guest_self_service',
        ]);

        Log::info('Booking cancelled via public token.', [
            'booking_id' => $booking->id,
            'page_id' => $booking->schedulingPage?->id,
        ]);

        $mailer = $this->resolveTransactionalMailer();
        $locale = $booking->resolveNotificationLocale();

        try {
            Mail::mailer($mailer)
                ->to($booking->customer_email)
                ->locale($locale)
                ->send(new BookingCancelled($booking));
        } catch (\Throwable $mailException) {
            Log::error("Booking cancellation mail failed [mailer={$mailer}]: " . $mailException->getMessage());
        }

        return response()->json([
            'message' => 'Booking cancelled.',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
            ],
        ]);
    }

    public function reschedule(Request $request, string $slug, string $token)
    {
        $validated = $request->validate([
            'start_at' => 'required|date',
            'timezone' => 'nullable|string',
        ]);

        $booking = Booking::query()
            ->with(['appointmentType', 'schedulingPage'])
            ->where('public_token', $token)
            ->whereHas('schedulingPage', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if (in_array($booking->status, ['cancelled', 'canceled'], true)) {
            return response()->json(['message' => 'Cancelled bookings cannot be rescheduled.'], 409);
        }

        $timezone = $validated['timezone'] ?? $booking->customer_timezone ?? 'UTC';
        $startTime = Carbon::parse($validated['start_at'], $timezone)->utc();

        if ($startTime->lte(now()->utc())) {
            return response()->json(['message' => 'Selected start time must be in the future.'], 422);
        }

        $durationMinutes = (int) ($booking->appointmentType?->duration_minutes ?? 0);
        $endTime = $startTime->copy()->addMinutes($durationMinutes);

        $conflicts = Booking::query()
            ->where('scheduling_page_id', $booking->scheduling_page_id)
            ->where('id', '!=', $booking->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_at', '<', $endTime)
                    ->where('end_at', '>', $startTime);
            })
            ->exists();

        if ($conflicts) {
            return response()->json(['message' => 'This time slot has just been taken.'], 409);
        }

        $booking->update([
            'start_at' => $startTime,
            'end_at' => $endTime,
            'status' => 'confirmed',
        ]);

        Log::info('Booking rescheduled via public token.', [
            'booking_id' => $booking->id,
            'page_id' => $booking->scheduling_page_id,
            'start_at' => $startTime->toIso8601String(),
        ]);

        $mailer = $this->resolveTransactionalMailer();
        $locale = $booking->resolveNotificationLocale();

        try {
            Mail::mailer($mailer)
                ->to($booking->customer_email)
                ->locale($locale)
                ->send(new BookingRescheduled($booking));
        } catch (\Throwable $mailException) {
            Log::error("Booking reschedule mail failed [mailer={$mailer}]: " . $mailException->getMessage());
        }

        return response()->json([
            'message' => 'Booking rescheduled.',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'start_at' => $booking->start_at?->toIso8601String(),
                'end_at' => $booking->end_at?->toIso8601String(),
            ],
        ]);
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
