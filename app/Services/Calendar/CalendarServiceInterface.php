<?php

namespace App\Services\Calendar;

use App\Models\Integration;
use Carbon\Carbon;

interface CalendarServiceInterface
{
    /**
     * Get busy slots from the external calendar for a date range.
     */
    public function getBusySlots(Integration $integration, Carbon $start, Carbon $end): array;

    /**
     * Push a booking to the external calendar.
     */
    public function createEvent(Integration $integration, $booking): string;
}
