<?php

namespace App\Filament\Resources\Instructors\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Instructors\InstructorResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateInstructor extends CreateRecord
{
    protected static string $resource = InstructorResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $existing = User::withTrashed()->where('email', $data['email'])->first();

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update(collect($data)->except('password')->toArray());
            if (!empty($data['password'])) {
                $existing->update(['password' => $data['password']]);
            }
            return $existing;
        }

        return parent::handleRecordCreation($data);
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles(['Instructor']);
    }


    protected function getRedirectUrl(): string
    {
        return InstructorResource::getUrl('index');
    }
}

