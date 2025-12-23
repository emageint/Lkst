<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\CourseStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingsTable

{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.name')
                    ->label('Course Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.full_name')
                    ->label('Customer Name')
                    ->searchable([ 'first_name', 'last_name' ])
                    ->sortable(),

                TextColumn::make('tutor.full_name')
                    ->label('Instructor')
                    ->searchable([ 'first_name', 'last_name' ])
                    ->sortable(),

                TextColumn::make('job_datetime')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('training_location')
                    ->label('Location')
                    ->searchable([
                        'training_location_line1',
                        'training_location_line2',
                        'training_location_line3',
                        'training_location_city',
                        'training_location_postcode',
                    ])
                    ->sortable(query: function ($query, $direction) {
                        return $query
                            ->orderBy('training_location_city', $direction)
                            ->orderBy('training_location_postcode', $direction);
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                // Add filters here if needed
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
