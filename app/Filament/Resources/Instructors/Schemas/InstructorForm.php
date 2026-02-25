<?php

namespace App\Filament\Resources\Instructors\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Novadaemon\FilamentCombobox\Combobox;


class InstructorForm
{
    public static function configure(Schema $schema): Schema
    {
        
        return $schema
            ->components([
                Section::make('')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
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

                        ColorPicker::make('color')
                            ->label('Colour')
                            ->required(),

                        Section::make('')
                            ->schema([
                                Combobox::make('courses')
                                    ->label('Courses the instructor can teach')
                                    ->relationship('courses', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull()
                                    ->extraAttributes([ 'class' => 'cbx-tall' ]),
                            ])
                            ->extraAttributes([ 'class' => 'cbx-tall' ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
