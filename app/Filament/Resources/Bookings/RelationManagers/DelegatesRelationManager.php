<?php

namespace App\Filament\Resources\Bookings\RelationManagers;

use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;


class DelegatesRelationManager extends RelationManager
{
    protected static string $relationship = 'delegates';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable([ 'first_name', 'last_name' ])
                    ->sortable([ 'first_name', 'last_name' ]),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
            ])
            ->headerActions([

                CreateAction::make()
                    ->label('Add Delegate')
                    ->using(function (array $data, DelegatesRelationManager $livewire) {
                        $booking = $livewire->getOwnerRecord();

                        $user = User::withTrashed()->where('email', $data['email'])->first();

                        if ($user && !$user->trashed() && $booking->delegates()->where('users.id', $user->id)->exists()) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'mountedActionsData.0.email' => 'This delegate is already added to this booking.',
                            ]);
                        }

                        if ($user && $user->trashed()) {
                            $user->restore();
                        }

                        if (!$user) {
                            $user = new User();
                            $user->first_name = $data['first_name'];
                            $user->last_name = $data['last_name'];
                            $user->email = $data['email'];
                            $user->password = bcrypt(Str::password(12));
                            $user->save();
                        }

                        if (!$user->hasRole('Learner')) {
                            $user->assignRole('Learner');
                        }

                        $booking->delegates()->syncWithoutDetaching([ $user->id ]);

                        return $user;
                    }),
            ])
            ->recordActions([
                DetachAction::make()
                    ->iconButton()
                    ->modalHeading('Remove delegate')
                    ->modalDescription('Are you sure you want to remove this delegate from the booking?')
                    ->modalSubmitActionLabel('Remove'),
            ])
            ->defaultSort('first_name');
    }

    
}
