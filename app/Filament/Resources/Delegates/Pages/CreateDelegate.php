<?php

namespace App\Filament\Resources\Delegates\Pages;

use App\Filament\Resources\Delegates\DelegateResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateDelegate extends CreateRecord
{
    protected static string $resource = DelegateResource::class;

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
}
