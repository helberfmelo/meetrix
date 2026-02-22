<?php

return [
    'booking' => [
        'confirmed' => [
            'subject' => 'Booking confirmed: :service',
            'title' => 'Booking confirmed',
            'intro' => 'Hi :name, your booking is confirmed.',
            'service' => 'Service',
            'datetime' => 'Date/Time',
            'status' => 'Status',
            'manage' => 'Manage booking',
            'signoff' => 'Thanks, :app',
        ],
        'cancelled' => [
            'subject' => 'Booking cancelled: :service',
            'title' => 'Booking cancelled',
            'intro' => 'Hi :name, your booking was cancelled.',
            'service' => 'Service',
            'datetime' => 'Date/Time',
            'status' => 'Status',
            'signoff' => 'Thanks, :app',
        ],
        'rescheduled' => [
            'subject' => 'Booking rescheduled: :service',
            'title' => 'Booking rescheduled',
            'intro' => 'Hi :name, your booking was rescheduled.',
            'service' => 'Service',
            'datetime' => 'Date/Time',
            'status' => 'Status',
            'signoff' => 'Thanks, :app',
        ],
    ],
];
