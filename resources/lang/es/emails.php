<?php

return [
    'booking' => [
        'confirmed' => [
            'subject' => 'Reserva confirmada: :service',
            'title' => 'Reserva confirmada',
            'intro' => 'Hola :name, tu reserva fue confirmada.',
            'service' => 'Servicio',
            'datetime' => 'Fecha/Hora',
            'status' => 'Estado',
            'manage' => 'Gestionar reserva',
            'signoff' => 'Gracias, :app',
        ],
        'cancelled' => [
            'subject' => 'Reserva cancelada: :service',
            'title' => 'Reserva cancelada',
            'intro' => 'Hola :name, tu reserva fue cancelada.',
            'service' => 'Servicio',
            'datetime' => 'Fecha/Hora',
            'status' => 'Estado',
            'signoff' => 'Gracias, :app',
        ],
        'rescheduled' => [
            'subject' => 'Reserva reprogramada: :service',
            'title' => 'Reserva reprogramada',
            'intro' => 'Hola :name, tu reserva fue reprogramada.',
            'service' => 'Servicio',
            'datetime' => 'Fecha/Hora',
            'status' => 'Estado',
            'signoff' => 'Gracias, :app',
        ],
    ],
];
