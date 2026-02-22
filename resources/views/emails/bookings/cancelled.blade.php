<h1>{{ __('emails.booking.cancelled.title') }}</h1>
<p>{{ __('emails.booking.cancelled.intro', ['name' => $booking->customer_name]) }}</p>

<p><strong>{{ __('emails.booking.cancelled.service') }}:</strong> {{ $booking->appointmentType->name ?? 'Session' }}</p>
<p><strong>{{ __('emails.booking.cancelled.datetime') }}:</strong> {{ optional($booking->start_at)->format('d/m/Y H:i') }}</p>
<p><strong>{{ __('emails.booking.cancelled.status') }}:</strong> {{ strtoupper($booking->status) }}</p>

<p>{{ __('emails.booking.cancelled.signoff', ['app' => config('app.name')]) }}</p>
