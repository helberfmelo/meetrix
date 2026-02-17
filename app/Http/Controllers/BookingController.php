<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'page_slug' => 'required|exists:pages,slug',
            'start_time' => 'required|date|after:now',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'meta' => 'nullable|array',
        ]);

        $page = Page::where('slug', $request->page_slug)->firstOrFail();

        // Calculate End Time based on Page Config
        // Default 30 mins if not set
        $duration = $page->config['duration'] ?? 30; 
        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addMinutes($duration);

        // Simple Availability Check (Overlapping bookings)
        $conflicts = Booking::where('page_id', $page->id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<', $startTime)
                            ->where('end_time', '>', $endTime);
                      });
            })
            ->exists();

        if ($conflicts) {
            return response()->json(['message' => 'Time slot unavailable'], 409);
        }

        $booking = Booking::create([
            'page_id' => $page->id,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'confirmed', // Auto-confirm for MVP
            'meta' => $request->meta,
        ]);

        // TODO: Dispatch Notification Job

        return response()->json($booking, 201);
    }

    /**
     * List bookings for a page (Admin view).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant; // derived from relationship if set up

        $bookings = Booking::whereHas('page', function ($query) use ($tenant) {
            $query->where('tenant_id', $tenant->id);
        })->with('page')->orderBy('start_time', 'desc')->paginate(15);

        return response()->json($bookings);
    }
}
