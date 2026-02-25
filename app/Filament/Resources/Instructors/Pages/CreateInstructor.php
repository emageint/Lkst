<?php

namespace App\Filament\Resources\Instructors\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Instructors\InstructorResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateInstructor extends CreateRecord
{
    protected static string $resource = InstructorResource::class;

    protected function afterCreate(): void
    {
        $this->record->assignRole('Instructor');
    }


    protected function getRedirectUrl(): string
    {
        return InstructorResource::getUrl('index');
    }
}

