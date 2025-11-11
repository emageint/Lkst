<?php

namespace App\Filament\Resources\Tutors;

use App\Filament\Resources\Tutors\Pages\ManageTutors;
use App\Models\Holiday;
use App\Models\Tutor;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class TutorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    protected static ?string $navigationLabel = 'Tutors';
    protected static ?string $modelLabel = 'Tutors';
    protected static ?string $recordTitleAttribute = 'Tutor';
    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
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
                    ->scopedUnique()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn(?string $state): bool => filled($state))
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                TextInput::make('address_line1')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address_line2')
                    ->maxLength(255),
                TextInput::make('address_line3')
                    ->maxLength(255),
                TextInput::make('city')
                    ->required()
                    ->maxLength(255),
                TextInput::make('postcode')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(User::query()->whereHas('roles', function ($query) {
                $query->where('name', 'Tutor');
            }))
            ->recordTitleAttribute('Tutor')
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->sortable([ 'first_name',
                        'last_name' ])
                    ->searchable([ 'first_name', 'last_name' ]),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),
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
            'index' => ManageTutors::route('/'),
        ];
    }
}
