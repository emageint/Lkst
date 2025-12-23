<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Mail\BookingUpdateMail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function getRedirectUrl(): string
    {
        return BookingResource::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $booking = $this->record;

        $url = URL::signedRoute('public.booking.form', [
            'booking' => $booking->id,
        ]);


        Mail::to($booking->customer->email)->send(
            new BookingUpdateMail($url)
        );
    }

}
