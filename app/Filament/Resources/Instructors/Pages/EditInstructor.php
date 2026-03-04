<?php

namespace App\Filament\Resources\Instructors\Pages;

use App\Filament\Resources\Instructors\InstructorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstructor extends EditRecord
{
    protected static string $resource = InstructorResource::class;

    public function getTitle(): string
    {
        return $this->record->full_name ?? 'Edit Instructor';
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Instructor saved';
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}

