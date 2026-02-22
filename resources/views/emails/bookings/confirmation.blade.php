<h1>{{ __('emails.booking.confirmed.title') }}</h1>
<p>{{ __('emails.booking.confirmed.intro', ['name' => $booking->customer_name]) }}</p>

<p><strong>{{ __('emails.booking.confirmed.service') }}:</strong> {{ $booking->appointmentType->name ?? 'Session' }}</p>
<p><strong>{{ __('emails.booking.confirmed.datetime') }}:</strong> {{ optional($booking->start_at)->format('d/m/Y H:i') }}</p>
<p><strong>{{ __('emails.booking.confirmed.status') }}:</strong> {{ strtoupper($booking->status) }}</p>

@if (!empty($manageUrl))
<p><strong>{{ __('emails.booking.confirmed.manage') }}:</strong> {{ $manageUrl }}</p>
@endif

<p>{{ __('emails.booking.confirmed.signoff', ['app' => config('app.name')]) }}</p>
