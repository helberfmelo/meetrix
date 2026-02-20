<?php

namespace App\Http\Controllers;

use App\Models\SchedulingPage;
use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $teamIds = $user->teams ? $user->teams->pluck('id') : collect([]);

        // Aggregated Stats
        $pageIds = SchedulingPage::where('user_id', $user->id);
        
        if ($teamIds->isNotEmpty()) {
            $pageIds = $pageIds->orWhereIn('team_id', $teamIds);
        }
        
        $pageIds = $pageIds->pluck('id');

        $views = SchedulingPage::whereIn('id', $pageIds)->sum('views');
        $clicks = SchedulingPage::whereIn('id', $pageIds)->sum('slot_clicks');
        $bookings = Booking::whereIn('scheduling_page_id', $pageIds)->count();
        $confirmedBookings = Booking::whereIn('scheduling_page_id', $pageIds)
            ->where('status', 'confirmed')
            ->count();

        $activePages = SchedulingPage::whereIn('id', $pageIds)
            ->where('is_active', true)
            ->count();

        $upcomingBookings = Booking::whereIn('scheduling_page_id', $pageIds)
            ->where('start_at', '>', now())
            ->where('status', 'confirmed')
            ->count();

        return response()->json([
            'funnel' => [
                'views' => $views,
                'clicks' => $clicks,
                'bookings' => $bookings,
                'conversion_rate' => $views > 0 ? round(($bookings / $views) * 100, 1) : 0,
            ],
            'stats' => [
                'total_bookings' => $bookings,
                'active_pages' => $activePages,
                'upcoming' => $upcomingBookings,
                'confirmed_rate' => $bookings > 0 ? round(($confirmedBookings / $bookings) * 100, 1) : 0,
            ]
        ]);
    }
}
