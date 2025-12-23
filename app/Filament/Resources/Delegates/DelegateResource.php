<?php

namespace App\Filament\Resources\Delegates;

use App\Filament\Resources\Delegates\Pages\CreateDelegate;
use App\Filament\Resources\Delegates\Pages\EditDelegate;
use App\Filament\Resources\Delegates\Pages\ListDelegates;
use App\Filament\Resources\Learners\LearnerResource;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
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
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;

class DelegateResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?string $navigationLabel = 'Delegates';
    protected static ?string $modelLabel = 'Delegate';
    protected static ?int $navigationSort = 40; // after Courses (30)

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()
                ->schema([
                    Section::make('Personal Information')
                        ->columns(2)
                        ->columnSpan([ 'lg' => fn(?User $record) => $record === null ? 3 : 2 ])
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
                        ->columns(2)
                        ->columnSpan([ 'lg' => fn(?User $record) => $record === null ? 3 : 2 ])
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
                ])
                ->columnSpan([ 'lg' => fn(?User $record) => $record === null ? 3 : 2 ]),
            Section::make()
                ->schema([
                    TextEntry::make('created_at')
                        ->state(fn(User $record): ?string => $record->created_at?->diffForHumans()),

                    TextEntry::make('updated_at')
                        ->label('Last modified at')
                        ->state(fn(User $record): ?string => $record->updated_at?->diffForHumans()),

                    TextEntry::make('editor.full_name')
                        ->label('Last modified by')
                        ->state(fn(User $record): ?string => $record->editor?->full_name),
                ])
                ->columnSpan([ 'lg' => 1 ])
                ->hidden(fn(?User $record) => $record === null),
        ])->columns(3);
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
                TextColumn::make('courses.name')
                    ->label('Courses')
                    ->sortable(),
            ])->recordUrl(fn($record) => DelegateResource::getUrl('courses', [ 'record' => $record ]))
            ->recordActions([
                Action::make('courses')
                    ->label('Courses')
                    ->icon('heroicon-o-academic-cap')
                    ->iconButton()
                    ->color('info')
                    ->url(fn($record) => DelegateResource::getUrl('courses', [ 'record' => $record ])),

                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDelegates::route('/'),
            'create' => CreateDelegate::route('/create'),
            'edit' => EditDelegate::route('/{record}/edit'),
            'courses' => Pages\ManageDelegateCourses::route('/{record}/courses'),
        ];
    }

}
