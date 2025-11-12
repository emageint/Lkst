<?php

namespace App\Filament\Resources\CourseCategories\Pages;

use App\Filament\Resources\CourseCategories\CourseCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCourseCategories extends ManageRecords
{
    protected static string $resource = CourseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
