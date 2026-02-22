<h1>{{ __('emails.booking.rescheduled.title') }}</h1>
<p>{{ __('emails.booking.rescheduled.intro', ['name' => $booking->customer_name]) }}</p>

<p><strong>{{ __('emails.booking.rescheduled.service') }}:</strong> {{ $booking->appointmentType->name ?? 'Session' }}</p>
<p><strong>{{ __('emails.booking.rescheduled.datetime') }}:</strong> {{ optional($booking->start_at)->format('d/m/Y H:i') }}</p>
<p><strong>{{ __('emails.booking.rescheduled.status') }}:</strong> {{ strtoupper($booking->status) }}</p>

<p>{{ __('emails.booking.rescheduled.signoff', ['app' => config('app.name')]) }}</p>
