<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ExternalCalendarAccount;
use App\Services\Outlook\OutlookGraphService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResyncOutlookEvents extends Command
{
    protected $signature = 'bookings:resync-outlook';

    protected $description = 'Re-sync all confirmed bookings with Outlook calendar events';

    public function handle(): int
    {
        $service = app(OutlookGraphService::class);

        $bookings = Booking::query()
            ->where('status', BookingStatus::Confirmed)
            ->whereNotNull('outlook_event_id')
            ->whereNotNull('instructor_id')
            ->whereNotNull('start')
            ->whereNotNull('end')
            ->get();

        $this->info("Found {$bookings->count()} confirmed bookings with Outlook events.");

        $updated = 0;

        foreach ($bookings as $booking) {
            $account = ExternalCalendarAccount::query()
                ->where('user_id', $booking->instructor_id)
                ->where('provider', 'outlook')
                ->first();

            if (!$account) {
                $this->warn("No Outlook account for instructor #{$booking->instructor_id}, skipping booking #{$booking->id}");
                continue;
            }

            $timezone = 'Europe/London';

            $start = $booking->start instanceof Carbon
                ? $booking->start
                : Carbon::parse($booking->start);

            $end = $booking->end instanceof Carbon
                ? $booking->end
                : Carbon::parse($booking->end);

            $payload = [
                'subject' => $booking->course?->name ?? 'Booking',
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $booking->notes ?? '',
                ],
                'start' => [
                    'dateTime' => $start->format('Y-m-d\TH:i:s'),
                    'timeZone' => $timezone,
                ],
                'end' => [
                    'dateTime' => $end->format('Y-m-d\TH:i:s'),
                    'timeZone' => $timezone,
                ],
                'location' => [
                    'displayName' => $booking->training_location ?? '',
                ],
            ];

            try {
                $service->updateEvent($account, $booking->outlook_event_id, $payload);
                $updated++;
                $this->line("✓ Booking #{$booking->id} updated");
            } catch (\Exception $e) {
                $this->error("✗ Booking #{$booking->id} failed: {$e->getMessage()}");
            }
        }

        $this->info("Done. Updated {$updated}/{$bookings->count()} events.");

        return self::SUCCESS;
    }
}
