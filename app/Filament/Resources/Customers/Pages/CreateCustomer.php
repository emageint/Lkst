<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

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
        $this->record->syncRoles(['Customer']);
    }

    protected function getRedirectUrl(): string
    {
        return CustomerResource::getUrl('index');
    }
}
