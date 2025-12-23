<?php

namespace App\Filament\Resources\Delegates\Pages;

use App\Filament\Resources\Delegates\DelegateResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Enums\ContentTabPosition;
use Filament\Schemas\Components\Tabs\Tab;

class EditDelegate extends EditRecord
{
    protected static string $resource = DelegateResource::class;

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

