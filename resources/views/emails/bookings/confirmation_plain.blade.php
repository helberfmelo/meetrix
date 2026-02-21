Agendamento confirmado

Ola {{ $booking->customer_name }},

Seu agendamento foi confirmado com sucesso.

Servico: {{ $booking->appointmentType->name ?? 'Sessao' }}
Data/Hora: {{ optional($booking->start_at)->format('d/m/Y H:i') }}
Status: {{ strtoupper($booking->status) }}

Obrigado,
{{ config('app.name') }}
