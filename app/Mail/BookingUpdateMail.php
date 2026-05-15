<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $url;
    public Booking $booking;

    public function __construct(string $url, Booking $booking)
    {
        $this->url = $url;
        $this->booking = $booking;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update Your Booking Details',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking-update',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
