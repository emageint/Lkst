<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    public function getTitle(): string
    {
        return $this->record->full_name ?? 'Edit Customer';
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Customer saved';
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}

