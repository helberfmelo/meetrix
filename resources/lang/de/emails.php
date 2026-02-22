<?php

return [
    'booking' => [
        'confirmed' => [
            'subject' => 'Buchung bestätigt: :service',
            'title' => 'Buchung bestätigt',
            'intro' => 'Hallo :name, Ihre Buchung wurde bestätigt.',
            'service' => 'Service',
            'datetime' => 'Datum/Uhrzeit',
            'status' => 'Status',
            'manage' => 'Buchung verwalten',
            'signoff' => 'Danke, :app',
        ],
        'cancelled' => [
            'subject' => 'Buchung storniert: :service',
            'title' => 'Buchung storniert',
            'intro' => 'Hallo :name, Ihre Buchung wurde storniert.',
            'service' => 'Service',
            'datetime' => 'Datum/Uhrzeit',
            'status' => 'Status',
            'signoff' => 'Danke, :app',
        ],
        'rescheduled' => [
            'subject' => 'Buchung verschoben: :service',
            'title' => 'Buchung verschoben',
            'intro' => 'Hallo :name, Ihre Buchung wurde verschoben.',
            'service' => 'Service',
            'datetime' => 'Datum/Uhrzeit',
            'status' => 'Status',
            'signoff' => 'Danke, :app',
        ],
    ],
];
