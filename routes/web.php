<?php

use App\Enums\BookingStatus;
use App\Filament\Pages\PublicBookingForm;
use App\Http\Controllers\OutlookOAuthController;
use App\Http\Controllers\OutlookWebhookController;
use App\Mail\BookingUpdateMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    $bookings = Booking::query()
        ->where('status', BookingStatus::Pending)
        ->whereNotNull('form_expires_at')
        ->where('form_expires_at', '<=', now())->get();
    (new \App\Jobs\ExpireBookings)->handle();
    dd($bookings);
})
    ->name('test');
Route::get('/booking/update/{booking}', PublicBookingForm::class)
    ->name('public.booking.form');

Route::get('/thank-you', function () {
    return view('filament-panels::components.layout.simple', [
        'slot' => view('filament.thank-you'),
    ]);
})->name('thank-you');


Route::get('/booking/expired', function () {
    return view('filament-panels::components.layout.simple', [
        'slot' => view('filament.booking-expired'),
    ]);
})->name('booking.expired');

Route::post('/webhooks/outlook', OutlookWebhookController::class)->name('webhooks.outlook');

Route::middleware('auth')->group(function () {
    Route::get('/outlook/connect', [ OutlookOAuthController::class, 'connect' ])
        ->name('outlook.connect');
    Route::get('/outlook/callback', [ OutlookOAuthController::class, 'callback' ])
        ->name('outlook.callback');
});

