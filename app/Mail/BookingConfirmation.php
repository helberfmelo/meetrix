<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use App\Models\Booking;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $manageUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking->load(['schedulingPage', 'appointmentType']);
        $token = $this->booking->public_token;
        $slug = $this->booking->schedulingPage?->slug;
        $this->manageUrl = ($token && $slug)
            ? url("/p/{$slug}/manage?token={$token}")
            : null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.booking.confirmed.subject', [
                'service' => $this->booking->appointmentType->name,
            ]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bookings.confirmation',
            text: 'emails.bookings.confirmation_plain',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
