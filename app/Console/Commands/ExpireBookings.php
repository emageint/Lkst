<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Mail\BookingReminderMail;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;


class ExpireBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Mark pending bookings with expired form links as expired, and send reminders after 24h';

    public function handle(): void
    {
        Booking::query()
            ->where('status', BookingStatus::Pending)
            ->whereNotNull('form_expires_at')
            ->where('form_expires_at', '<=', now())
            ->each(function (Booking $booking): void {
                $booking->updateQuietly([ 'status' => BookingStatus::Expired ]);
            });

        Booking::query()
            ->where('status', BookingStatus::Pending)
            ->whereNotNull('customer_id')
            ->whereNull('reminder_sent_at')
            ->where('created_at', '<=', now()->subHours(24))
            ->whereNotNull('form_expires_at')
            ->where('form_expires_at', '>', now())
            ->with('customer')
            ->each(function (Booking $booking): void {
                if (!$booking->customer) {
                    return;
                }

                $url = URL::signedRoute('public.booking.form', [
                    'booking' => $booking->id,
                ]);

                Mail::to($booking->customer->email)->send(new BookingReminderMail($url));

                $booking->updateQuietly([ 'reminder_sent_at' => now() ]);
            });
    }
}
