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

                TextColumn::make('instructor.full_name')
                    ->label('Instructor')
                    ->searchable([ 'first_name', 'last_name' ])
                    ->sortable(),

                TextColumn::make('start')
                    ->label('Start')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),


                TextColumn::make('location')
                    ->label('Location')
                    ->getStateUsing(fn($record) => $record->location_lkst_yard
                        ? 'LKST Yard'
                        : $record->training_location
                    )
                    ->searchable([
                        'training_location_line1',
                        'training_location_city',
                        'training_location_postcode',
                    ])
                    ->wrap(),
                TextColumn::make('delegates_count')
                    ->label('Delegates')
                    ->counts('delegates')
                    ->formatStateUsing(fn($state, $record) => $state . ' / ' . ($record->max_delegates ?? '∞'))
                    ->sortable(),


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
