<?php

namespace App\Filament\Resources\Jobs\Schemas;

use App\Models\Course;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        // Course select
                        Select::make('course_id')
                            ->label('Course Name')
                            ->options(Course::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),

                        // Customer select
                        Select::make('customer_id')
                            ->label('Customer Name')
                            ->relationship(
                                name: 'customer',
                                titleAttribute: 'first_name',
                                modifyQueryUsing: fn($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'Customer'))
                            )
                            ->getOptionLabelFromRecordUsing(fn(User $record) => $record->first_name . ' ' . $record->last_name)
                            ->searchable([ 'first_name', 'last_name', 'email' ])
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        // Tutor select
                        Select::make('tutor_id')
                            ->label('Tutor')
                            ->relationship(
                                name: 'tutor',
                                titleAttribute: 'first_name',
                                modifyQueryUsing: fn($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'Tutor'))
                            )
                            ->getOptionLabelFromRecordUsing(fn(User $record) => $record->first_name . ' ' . $record->last_name)
                            ->searchable([ 'first_name', 'last_name', 'email' ])
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                        DateTimePicker::make('job_datetime')->label('Date')->required()->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false),
                        // Number of Learners
                        TextInput::make('number_of_learners')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->columnSpan(1),

                        // Training Location Address
                        TextInput::make('training_location_line1')
                            ->label('Training Location Address Line 1')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('training_location_line2')
                            ->label('Training Location Address Line 2')
                            ->columnSpan(1),

                        TextInput::make('training_location_line3')
                            ->label('Training Location Address Line 3')
                            ->columnSpan(1),

                        TextInput::make('training_location_city')
                            ->label('Training Location Town / City')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('training_location_postcode')
                            ->label('Training Location Postcode')
                            ->required()
                            ->columnSpan(1),
                    ])
            ]);
    }
}
