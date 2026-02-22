<?php

return [
    'booking' => [
        'confirmed' => [
            'subject' => 'Agendamento confirmado: :service',
            'title' => 'Agendamento confirmado',
            'intro' => 'Olá :name, seu agendamento foi confirmado.',
            'service' => 'Serviço',
            'datetime' => 'Data/Hora',
            'status' => 'Status',
            'manage' => 'Gerenciar agendamento',
            'signoff' => 'Obrigado, :app',
        ],
        'cancelled' => [
            'subject' => 'Agendamento cancelado: :service',
            'title' => 'Agendamento cancelado',
            'intro' => 'Olá :name, seu agendamento foi cancelado.',
            'service' => 'Serviço',
            'datetime' => 'Data/Hora',
            'status' => 'Status',
            'signoff' => 'Obrigado, :app',
        ],
        'rescheduled' => [
            'subject' => 'Agendamento reagendado: :service',
            'title' => 'Agendamento reagendado',
            'intro' => 'Olá :name, seu agendamento foi reagendado.',
            'service' => 'Serviço',
            'datetime' => 'Data/Hora',
            'status' => 'Status',
            'signoff' => 'Obrigado, :app',
        ],
    ],
];
