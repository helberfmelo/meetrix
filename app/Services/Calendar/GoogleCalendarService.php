<?php

namespace App\Services\Calendar;

use App\Models\Integration;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\FreeBusyRequest;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

class GoogleCalendarService implements CalendarServiceInterface
{
    public function getBusySlots(Integration $integration, Carbon $start, Carbon $end): array
    {
        $client = $this->getClient($integration);
        $service = new GoogleCalendar($client);

        $request = new FreeBusyRequest();
        $request->setTimeMin($start->toRfc3339String());
        $request->setTimeMax($end->toRfc3339String());
        $request->setItems([['id' => 'primary']]);

        $query = $service->freebusy->query($request);
        $busy = $query->getCalendars()['primary']->getBusy();

        return array_map(function ($item) {
            return [
                'start' => Carbon::parse($item->getStart()),
                'end' => Carbon::parse($item->getEnd()),
            ];
        }, $busy);
    }

    public function createEvent(Integration $integration, $booking): string
    {
        $client = $this->getClient($integration);
        $service = new GoogleCalendar($client);

        $event = new Event([
            'summary' => "Meetrix: {$booking->appointmentType->name}",
            'description' => "Booking confirmed for {$booking->customer_name}. Email: {$booking->customer_email}",
            'start' => new EventDateTime([
                'dateTime' => $booking->start_at->toRfc3339String(),
                'timeZone' => $booking->customer_timezone,
            ]),
            'end' => new EventDateTime([
                'dateTime' => $booking->end_at->toRfc3339String(),
                'timeZone' => $booking->customer_timezone,
            ]),
            'attendees' => [
                ['email' => $booking->customer_email],
            ],
        ]);

        $event = $service->events->insert('primary', $event);

        return $event->getId();
    }

    private function getClient(Integration $integration)
    {
        $client = new GoogleClient();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setAccessToken([
            'access_token' => $integration->token,
            'refresh_token' => $integration->refresh_token,
            'expires_in' => $integration->expires_at->diffInSeconds(now()),
            'created' => $integration->updated_at->timestamp,
        ]);

        if ($client->isAccessTokenExpired()) {
            if ($integration->refresh_token) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($integration->refresh_token);
                
                if (isset($newToken['access_token'])) {
                    $integration->update([
                        'token' => $newToken['access_token'],
                        'expires_at' => now()->addSeconds($newToken['expires_in']),
                    ]);
                }
            }
        }

        return $client;
    }
}
