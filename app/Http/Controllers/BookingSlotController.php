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

        // Get internal bookings
        $existingBookings = Booking::where('scheduling_page_id', $page->id)
            ->whereDate('start_at', $date->toDateString())
            ->whereIn('status', ['confirmed', 'pending'])
            ->get();

        // Get external busy slots
        $syncService = new \App\Services\Calendar\CalendarSyncService();
        $externalBusy = $syncService->getAllBusySlots($page->user, $date->copy()->startOfDay(), $date->copy()->endOfDay());

        $availableSlots = [];
        $duration = $appointmentType->duration_minutes;

        foreach ($rules as $rule) {
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $date->toDateString() . ' ' . $rule->start_time);
            $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $date->toDateString() . ' ' . $rule->end_time);

            $current = $startTime->copy();
            while ($current->copy()->addMinutes($duration)->lte($endTime)) {
                $slotStart = $current->copy();
                $slotEnd = $current->copy()->addMinutes($duration);

                // Check internal overlap
                $isBookedInternally = $existingBookings->contains(function ($booking) use ($slotStart, $slotEnd) {
                    return $slotStart->lt($booking->end_at) && $slotEnd->gt($booking->start_at);
                });

                // Check external overlap
                $isBookedExternally = collect($externalBusy)->contains(function ($busy) use ($slotStart, $slotEnd) {
                    return $slotStart->lt($busy['end']) && $slotEnd->gt($busy['start']);
                });

                if (!$isBookedInternally && !$isBookedExternally) {
                    $availableSlots[] = $slotStart->format('H:i');
                }

                $current->addMinutes(15);
            }
        }

        return response()->json($availableSlots);
    }
}
