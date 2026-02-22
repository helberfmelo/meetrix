<?php

return [
    'booking' => [
        'confirmed' => [
            'subject' => 'Prenotazione confermata: :service',
            'title' => 'Prenotazione confermata',
            'intro' => 'Ciao :name, la tua prenotazione è stata confermata.',
            'service' => 'Servizio',
            'datetime' => 'Data/Ora',
            'status' => 'Stato',
            'manage' => 'Gestire prenotazione',
            'signoff' => 'Grazie, :app',
        ],
        'cancelled' => [
            'subject' => 'Prenotazione annullata: :service',
            'title' => 'Prenotazione annullata',
            'intro' => 'Ciao :name, la tua prenotazione è stata annullata.',
            'service' => 'Servizio',
            'datetime' => 'Data/Ora',
            'status' => 'Stato',
            'signoff' => 'Grazie, :app',
        ],
        'rescheduled' => [
            'subject' => 'Prenotazione ripianificata: :service',
            'title' => 'Prenotazione ripianificata',
            'intro' => 'Ciao :name, la tua prenotazione è stata ripianificata.',
            'service' => 'Servizio',
            'datetime' => 'Data/Ora',
            'status' => 'Stato',
            'signoff' => 'Grazie, :app',
        ],
    ],
];
