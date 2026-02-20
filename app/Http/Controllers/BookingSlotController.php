<?php

namespace App\Http\Controllers;

use App\Models\SchedulingPage;
use App\Models\AppointmentType;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class BookingSlotController extends Controller
{
    /**
     * Get available slots for a specific page and date.
     */
    public function index(Request $request, $slug)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'appointment_type_id' => 'required|exists:appointment_types,id',
            'timezone' => 'nullable|timezone',
        ]);

        $date = Carbon::parse($request->date);
        $appointmentType = AppointmentType::findOrFail($request->appointment_type_id);
        
        $page = SchedulingPage::where('slug', $slug)
            ->where('is_active', true)
            ->with(['availabilityRules' => function($query) use ($date) {
                // Carbon dayOfWeek returns 0 (Sun) to 6 (Sat)
                // In our rules we might store it same or similar. 
                // Let's check rule structure.
                $query->whereJsonContains('days_of_week', $date->dayOfWeek);
            }])
            ->firstOrFail();

        $rules = $page->availabilityRules;
        if ($rules->isEmpty()) {
            return response()->json([]);
        }

        $viewerTimezone = $request->input('timezone') ?: ($rules->first()->timezone ?: config('app.timezone', 'UTC'));
        $dayStartUtc = Carbon::createFromFormat('Y-m-d H:i:s', $request->date . ' 00:00:00', $viewerTimezone)->utc();
        $dayEndUtc = Carbon::createFromFormat('Y-m-d H:i:s', $request->date . ' 23:59:59', $viewerTimezone)->utc();

        // Get internal bookings
        $existingBookings = Booking::where('scheduling_page_id', $page->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where(function ($query) use ($dayStartUtc, $dayEndUtc) {
                $query->where('start_at', '<', $dayEndUtc)
                    ->where('end_at', '>', $dayStartUtc);
            })
            ->get();

        // Get external busy slots
        $syncService = new \App\Services\Calendar\CalendarSyncService();
        $externalBusy = $syncService->getAllBusySlots($page->user, $dayStartUtc->copy(), $dayEndUtc->copy());

        $availableSlots = [];
        $duration = $appointmentType->duration_minutes;

        foreach ($rules as $rule) {
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $request->date . ' ' . $rule->start_time, $viewerTimezone);
            $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $request->date . ' ' . $rule->end_time, $viewerTimezone);

            $current = $startTime->copy();
            while ($current->copy()->addMinutes($duration)->lte($endTime)) {
                $slotStartUtc = $current->copy()->utc();
                $slotEndUtc = $current->copy()->addMinutes($duration)->utc();

                // Check internal overlap
                $isBookedInternally = $existingBookings->contains(function ($booking) use ($slotStartUtc, $slotEndUtc) {
                    $bookingStartUtc = Carbon::parse($booking->getRawOriginal('start_at'), 'UTC');
                    $bookingEndUtc = Carbon::parse($booking->getRawOriginal('end_at'), 'UTC');

                    return $slotStartUtc->lt($bookingEndUtc) && $slotEndUtc->gt($bookingStartUtc);
                });

                // Check external overlap
                $isBookedExternally = collect($externalBusy)->contains(function ($busy) use ($slotStartUtc, $slotEndUtc) {
                    $busyStartUtc = ($busy['start'] instanceof Carbon ? $busy['start']->copy() : Carbon::parse($busy['start']))->utc();
                    $busyEndUtc = ($busy['end'] instanceof Carbon ? $busy['end']->copy() : Carbon::parse($busy['end']))->utc();

                    return $slotStartUtc->lt($busyEndUtc) && $slotEndUtc->gt($busyStartUtc);
                });

                if (!$isBookedInternally && !$isBookedExternally) {
                    $availableSlots[] = $current->format('H:i');
                }

                $current->addMinutes(15);
            }
        }

        return response()->json($availableSlots);
    }
}
