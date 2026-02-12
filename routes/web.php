<?php

use App\Filament\Pages\PublicBookingForm;
use App\Http\Controllers\OutlookOAuthController;
use App\Http\Controllers\OutlookWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/booking/update/{booking}', PublicBookingForm::class)
    ->name('public.booking.form');

Route::get('/thank-you', function () {
    return view('filament-panels::components.layout.simple', [
        'slot' => view('filament.thank-you'),
    ]);
})->name('thank-you');

Route::post('/webhooks/outlook', OutlookWebhookController::class)->name('webhooks.outlook');

Route::middleware('auth')->group(function () {
    Route::get('/outlook/connect', [ OutlookOAuthController::class, 'connect' ])
        ->name('outlook.connect');
    Route::get('/outlook/callback', [ OutlookOAuthController::class, 'callback' ])
        ->name('outlook.callback');
});

