<?php

namespace App\Services\Calendar;

use App\Models\User;
use App\Models\Integration;
use Carbon\Carbon;

class CalendarSyncService
{
    /**
     * Get all busy slots from all user integrations for a date range.
     */
    public function getAllBusySlots(User $user, Carbon $start, Carbon $end): array
    {
        $allBusy = [];

        foreach ($user->integrations as $integration) {
            $service = $this->getService($integration->service);
            if ($service) {
                $busy = $service->getBusySlots($integration, $start, $end);
                $allBusy = array_merge($allBusy, $busy);
            }
        }

        return $allBusy;
    }

    /**
     * Push a booking to all relevant calendars.
     */
    public function syncBooking($booking)
    {
        $user = $booking->schedulingPage->user;

        foreach ($user->integrations as $integration) {
            $service = $this->getService($integration->service);
            if ($service) {
                $externalId = $service->createEvent($integration, $booking);
                // We could store the external_id if we want to support updates/deletes later
            }
        }
    }

    private function getService(string $service)
    {
        return match ($service) {
            'google' => new GoogleCalendarService(),
            // 'outlook' => new OutlookCalendarService(),
            default => null,
        };
    }
}
