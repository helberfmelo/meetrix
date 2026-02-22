<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRescheduled extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking->load(['schedulingPage', 'appointmentType']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.booking.rescheduled.subject', [
                'service' => $this->booking->appointmentType->name,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bookings.rescheduled',
            text: 'emails.bookings.rescheduled_plain',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
