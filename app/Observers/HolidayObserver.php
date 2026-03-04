<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\ExternalCalendarAccount;
use App\Models\Holiday;
use App\Services\Outlook\OutlookGraphService;


class HolidayObserver
{
    public function created(Holiday $holiday): void
    {
        $this->clearInstructorFromBookings($holiday);
    }

    public function updated(Holiday $holiday): void
    {
        $this->clearInstructorFromBookings($holiday);
    }

    private function clearInstructorFromBookings(Holiday $holiday): void
    {
        $bookings = Booking::where('instructor_id', $holiday->user_id)
            ->whereDate('start', '>=', $holiday->start_date)
            ->whereDate('start', '<=', $holiday->end_date)
            ->get();

        $account = ExternalCalendarAccount::query()
            ->where('user_id', $holiday->user_id)
            ->where('provider', 'outlook')
            ->first();

        foreach ($bookings as $booking) {
            if ($account && $booking->outlook_event_id) {
                app(OutlookGraphService::class)->deleteEvent($account, $booking->outlook_event_id);
            }

            $booking->outlook_event_id = null;
            $booking->instructor_id = null;
            $booking->saveQuietly();
        }
    }
}

