<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;


class ExpireBookings implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Booking::query()
            ->where('status', BookingStatus::Pending)
            ->whereNotNull('form_expires_at')
            ->where('form_expires_at', '<=', now())
            ->each(function (Booking $booking): void {
                $booking->updateQuietly([ 'status' => BookingStatus::Expired ]);
            });
    }
}
