{{ __('emails.booking.rescheduled.title') }}

{{ __('emails.booking.rescheduled.intro', ['name' => $booking->customer_name]) }}

{{ __('emails.booking.rescheduled.service') }}: {{ $booking->appointmentType->name ?? 'Session' }}
{{ __('emails.booking.rescheduled.datetime') }}: {{ optional($booking->start_at)->format('d/m/Y H:i') }}
{{ __('emails.booking.rescheduled.status') }}: {{ strtoupper($booking->status) }}

{{ __('emails.booking.rescheduled.signoff', ['app' => config('app.name')]) }}
