<?php

namespace App\Observers;

use App\Mail\BookingUpdateMail;
use App\Models\Booking;
use App\Models\ExternalCalendarAccount;
use App\Services\Outlook\OutlookGraphService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;


class BookingObserver
{
    public function created(Booking $booking): void
    {
        if ($booking->customer) {
            $url = URL::signedRoute('public.booking.form', [
                'booking' => $booking->id,
            ]);
            Mail::to($booking->customer->email)->send(new BookingUpdateMail($url));
        }

        $account = $this->getAccount($booking);
        if (!$account || $booking->start === null || $booking->end === null) {
            return;
        }

        $payload = $this->buildPayload($booking);
        $response = app(OutlookGraphService::class)->createEvent($account, $payload);

        if (!empty($response['id'])) {
            $booking->outlook_event_id = $response['id'];
            $booking->saveQuietly();
        }
    }

    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('outlook_event_id')) {
            return;
        }

        $account = $this->getAccount($booking);
        if (!$account || $booking->start === null || $booking->end === null) {
            return;
        }

        $payload = $this->buildPayload($booking);
        $service = app(OutlookGraphService::class);

        if ($booking->outlook_event_id) {
            $service->updateEvent($account, $booking->outlook_event_id, $payload);
            return;
        }

        $response = $service->createEvent($account, $payload);
        if (!empty($response['id'])) {
            $booking->outlook_event_id = $response['id'];
            $booking->saveQuietly();
        }
    }

    public function deleted(Booking $booking): void
    {
        $account = $this->getAccount($booking);
        if (!$account || !$booking->outlook_event_id) {
            return;
        }

        app(OutlookGraphService::class)->deleteEvent($account, $booking->outlook_event_id);
    }

    private function getAccount(Booking $booking): ?ExternalCalendarAccount
    {
        if (!$booking->instructor_id) {
            return null;
        }

        return ExternalCalendarAccount::query()
            ->where('user_id', $booking->instructor_id)
            ->where('provider', 'outlook')
            ->first();
    }

    private function buildPayload(Booking $booking): array
    {
        $timezone = config('app.timezone', 'UTC');

        return [
            'subject' => $booking->course?->name ?? 'Booking',
            'body' => [
                'contentType' => 'HTML',
                'content' => $booking->notes ?? '',
            ],
            'start' => [
                'dateTime' => $booking->start instanceof Carbon
                    ? $booking->start->toIso8601String()
                    : Carbon::parse($booking->start)->toIso8601String(),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $booking->end instanceof Carbon
                    ? $booking->end->toIso8601String()
                    : Carbon::parse($booking->end)->toIso8601String(),
                'timeZone' => $timezone,
            ],
            'location' => [
                'displayName' => $booking->training_location ?? '',
            ],
        ];
    }
}
