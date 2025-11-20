<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $recordTitleAttribute = 'user';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->scopedUnique()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn(?string $state): bool => filled($state))
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                Select::make('role_id')
                    ->label('Role')
                    ->options(\Spatie\Permission\Models\Role::pluck('name', 'id'))
                    ->required()
                    ->default(fn($record) => $record?->roles()->first()?->id)
                    ->afterStateHydrated(function ($state, $record, callable $set) {

                        if ($record && empty($state)) {
                            $set('role_id', $record->roles()->first()?->id);
                        }
                    })
                    ->afterStateUpdated(function ($state, $record) {
                        if ($record) {
                            $record->syncRoles([ $state ]);
                        }
                    })

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user')
            ->columns([

                TextColumn::make('full_name')
                    ->label('Name')->sortable([ 'first_name',
                        'last_name' ])->searchable([ 'first_name', 'last_name' ]),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
     
                TextColumn::make('roles.name')
                    ->badge()
                    ->label('Role')->sortable()->searchable(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
