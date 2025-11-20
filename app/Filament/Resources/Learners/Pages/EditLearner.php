<?php

namespace App\Filament\Resources\Learners\Pages;

use App\Filament\Resources\Learners\LearnerResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Enums\ContentTabPosition;
use Filament\Schemas\Components\Tabs\Tab;

class EditLearner extends EditRecord
{
    protected static string $resource = LearnerResource::class;

//    public function hasCombinedRelationManagerTabsWithContent(): bool
//    {
//        return true;
//    }
//
//    public function getContentTabLabel(): ?string
//    {
//        $record = $this->getRecord();
//        return $record?->full_name . ' Details' ?? 'Learner Details';
//
//    }

    public function getTitle(): string
    {
        $record = $this->getRecord();
        return $record?->full_name ?? 'Learner';
    }


}

