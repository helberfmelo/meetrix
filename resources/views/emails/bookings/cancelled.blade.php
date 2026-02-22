@component('mail::message')
# {{ __('emails.booking.cancelled.title') }}

{{ __('emails.booking.cancelled.intro', ['name' => $booking->customer_name]) }}

@component('mail::panel')
{{ __('emails.booking.cancelled.service') }}: {{ $booking->appointmentType->name ?? 'Session' }}

{{ __('emails.booking.cancelled.datetime') }}: {{ optional($booking->start_at)->format('d/m/Y H:i') }}

{{ __('emails.booking.cancelled.status') }}: {{ strtoupper($booking->status) }}
@endcomponent

{{ __('emails.booking.cancelled.signoff', ['app' => config('app.name')]) }}
@endcomponent
