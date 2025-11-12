<?php

namespace App\Filament\Resources\Learners;

use App\Filament\Resources\Learners\Pages\ManageLearners;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;

class LearnerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?string $navigationLabel = 'Learners';
    protected static ?string $modelLabel = 'Learner';
    protected static ?int $navigationSort = 40; // after Courses (30)

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Personal Information')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('title')
                        ->label('Title')
                        ->options([
                            'Mr' => 'Mr',
                            'Mrs' => 'Mrs',
                            'Miss' => 'Miss',
                            'Other' => 'Other',
                        ])
                        ->required(),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->scopedUnique()
                        ->required(),

                    TextInput::make('first_name')
                        ->label('First Name')
                        ->required(),

                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->required(fn(string $operation): bool => $operation === 'create')
                        ->dehydrated(fn(?string $state): bool => filled($state)),
                    
                    TextInput::make('last_name')
                        ->label('Last Name')
                        ->required(),

                ]),

            Section::make('Contact')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('address_line1')
                        ->label('Address Line 1')
                        ->required(),

                    TextInput::make('address_line2')
                        ->label('Address Line 2'),

                    TextInput::make('address_line3')
                        ->label('Address Line 3'),

                    TextInput::make('city')
                        ->label('Town / City')
                        ->required(),

                    TextInput::make('postcode')
                        ->label('Postcode')
                        ->required(),

                    TextInput::make('phone')
                        ->label('Tel Number')
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(User::query()->whereHas('roles', function ($query) {
                $query->where('name', 'Learner');
            }))
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->sortable([ 'first_name', 'last_name' ])
                    ->searchable([ 'first_name', 'last_name' ]),
                TextColumn::make('email')
                    ->label('Email Address')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Tel Number')
                    ->sortable()
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLearners::route('/'),
        ];
    }
}
