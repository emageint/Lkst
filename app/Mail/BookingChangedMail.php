<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public array $changes;

    public function __construct(Booking $booking, array $changes)
    {
        $this->booking = $booking;
        $this->changes = $changes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Booking Has Been Updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking-changed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
