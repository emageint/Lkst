<?php

use App\Filament\Pages\PublicBookingForm;
use Illuminate\Support\Facades\Route;

Route::get('/booking/update/{booking}', PublicBookingForm::class)
    ->name('public.booking.form');

Route::get('/thank-you', function () {
    return view('filament-panels::components.layout.simple', [
        'slot' => view('filament.thank-you'),
    ]);
})->name('thank-you');
