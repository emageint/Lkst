<?php

namespace App\Observers;

use App\Mail\BookingChangedMail;
use App\Mail\BookingUpdateMail;
use App\Models\Booking;
use App\Models\ExternalCalendarAccount;
use App\Services\BookingScheduleService;
use App\Services\Outlook\OutlookGraphService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        if ($booking->booking_mode === 'misc') {
            $this->syncOutlookEvent($booking);
            return;
        }

        if ($booking->customer) {
            $expiresAt = app(BookingScheduleService::class)->addBusinessHours(Carbon::now(), 48);
            $booking->form_expires_at = $expiresAt;
            $booking->saveQuietly();

            $url = URL::signedRoute('public.booking.form', [
                'booking' => $booking->id,
            ]);
            $additionalEmails = $booking->customer->emailRecipients->pluck('email')->all();
            Mail::to($booking->customer->email)
                ->cc($additionalEmails)
                ->send(new BookingUpdateMail($url, $booking));
        }

    }

    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('outlook_event_id')) {
            return;
        }

        if ($booking->booking_mode !== 'misc') {
            $this->notifyCustomerIfKeyFieldsChanged($booking);
        }

        $service = app(OutlookGraphService::class);
        $statusChanged = $booking->wasChanged('status');
        $isConfirmed = $booking->status === \App\Enums\BookingStatus::Confirmed;

        if ($statusChanged && !$isConfirmed) {
            if ($booking->outlook_event_id) {
                $account = $this->getAccount($booking);
                if ($account) {
                    $service->deleteEvent($account, $booking->outlook_event_id);
                }
                $booking->outlook_event_id = null;
                $booking->saveQuietly();
            }
            return;
        }

        if (!$isConfirmed) {
            return;
        }

        $instructorChanged = $booking->wasChanged('instructor_id');

        if ($instructorChanged && $booking->outlook_event_id) {
            $oldInstructorId = $booking->getOriginal('instructor_id');
            $oldAccount = $oldInstructorId
                ? ExternalCalendarAccount::query()
                    ->where('user_id', $oldInstructorId)
                    ->where('provider', 'outlook')
                    ->first()
                : null;

            if ($oldAccount) {
                $service->deleteEvent($oldAccount, $booking->outlook_event_id);
            }

            $booking->outlook_event_id = null;
            $booking->saveQuietly();
        }

        $account = $this->getAccount($booking);
        if (!$account || $booking->start === null || $booking->end === null) {
            return;
        }

        $payload = $this->buildPayload($booking);

        $currentEventId = $booking->getOriginal('outlook_event_id') ?? $booking->outlook_event_id;

        if ($currentEventId && !$instructorChanged) {
            $service->updateEvent($account, $currentEventId, $payload);
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

    private function notifyCustomerIfKeyFieldsChanged(Booking $booking): void
    {
        if (!$booking->delegates_submitted || !$booking->customer) {
            return;
        }

        $trackedFields = [
            'start',
            'end',
            'training_location_line1',
            'training_location_line2',
            'training_location_line3',
            'training_location_city',
            'training_location_postcode',
            'location_lkst_yard',
            'course_id',
        ];

        $changed = array_intersect($trackedFields, array_keys($booking->getChanges()));

        if (empty($changed)) {
            return;
        }

        $labels = [
            'start' => 'Start date/time',
            'end' => 'End date/time',
            'training_location_line1' => 'Location',
            'training_location_line2' => 'Location',
            'training_location_line3' => 'Location',
            'training_location_city' => 'Location',
            'training_location_postcode' => 'Location',
            'location_lkst_yard' => 'Location',
            'course_id' => 'Course',
        ];

        $changes = collect($changed)
            ->map(fn($field) => $labels[$field] ?? $field)
            ->unique()
            ->values()
            ->all();

        $booking->load('course');
        $additionalEmails = $booking->customer->emailRecipients->pluck('email')->all();
        Mail::to($booking->customer->email)
            ->cc($additionalEmails)
            ->send(new BookingChangedMail($booking, $changes));
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

    private function syncOutlookEvent(Booking $booking): void
    {
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

    private function buildPayload(Booking $booking): array
    {
        $timezone = 'Europe/London';

        $start = $booking->start instanceof Carbon
            ? $booking->start
            : Carbon::parse($booking->start);

        $end = $booking->end instanceof Carbon
            ? $booking->end
            : Carbon::parse($booking->end);

        return [
            'subject' => $booking->booking_mode === 'misc'
                ? ($booking->title ?? 'Booking')
                : ($booking->course?->name ?? 'Booking'),
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
    }
}
