@component('mail::message')
# {{ __('emails.booking.confirmed.title') }}

{{ __('emails.booking.confirmed.intro', ['name' => $booking->customer_name]) }}

@component('mail::panel')
{{ __('emails.booking.confirmed.service') }}: {{ $booking->appointmentType->name ?? 'Session' }}

{{ __('emails.booking.confirmed.datetime') }}: {{ optional($booking->start_at)->format('d/m/Y H:i') }}

{{ __('emails.booking.confirmed.status') }}: {{ strtoupper($booking->status) }}
@endcomponent

@if (!empty($manageUrl))
{{ __('emails.booking.confirmed.manage') }}: {{ $manageUrl }}
@endif

{{ __('emails.booking.confirmed.signoff', ['app' => config('app.name')]) }}
@endcomponent
