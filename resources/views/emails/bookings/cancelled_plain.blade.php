{{ __('emails.booking.cancelled.title') }}

{{ __('emails.booking.cancelled.intro', ['name' => $booking->customer_name]) }}

{{ __('emails.booking.cancelled.service') }}: {{ $booking->appointmentType->name ?? 'Session' }}
{{ __('emails.booking.cancelled.datetime') }}: {{ optional($booking->start_at)->format('d/m/Y H:i') }}
{{ __('emails.booking.cancelled.status') }}: {{ strtoupper($booking->status) }}

{{ __('emails.booking.cancelled.signoff', ['app' => config('app.name')]) }}
