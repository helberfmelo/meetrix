<?php

return [
    'booking' => [
        'confirmed' => [
            'subject' => 'Réservation confirmée : :service',
            'title' => 'Réservation confirmée',
            'intro' => 'Bonjour :name, votre réservation a été confirmée.',
            'service' => 'Service',
            'datetime' => 'Date/Heure',
            'status' => 'Statut',
            'manage' => 'Gérer la réservation',
            'signoff' => 'Merci, :app',
        ],
        'cancelled' => [
            'subject' => 'Réservation annulée : :service',
            'title' => 'Réservation annulée',
            'intro' => 'Bonjour :name, votre réservation a été annulée.',
            'service' => 'Service',
            'datetime' => 'Date/Heure',
            'status' => 'Statut',
            'signoff' => 'Merci, :app',
        ],
        'rescheduled' => [
            'subject' => 'Réservation replanifiée : :service',
            'title' => 'Réservation replanifiée',
            'intro' => 'Bonjour :name, votre réservation a été replanifiée.',
            'service' => 'Service',
            'datetime' => 'Date/Heure',
            'status' => 'Statut',
            'signoff' => 'Merci, :app',
        ],
    ],
];
