<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\BookingStatus;
use App\Enums\CourseStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable

{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.name')
                    ->label('Course Name')
                    ->getStateUsing(fn ($record) => $record->booking_mode === 'misc'
                        ? $record->title
                        : $record->course?->name
                    )
                    ->searchable(['title'])
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
                SelectFilter::make('booking_mode')
                    ->label('Mode')
                    ->options([
                        'course' => 'Course Booking',
                        'misc' => 'Miscellaneous',
                    ]),
            ])
            ->recordActions([
                Action::make('complete')
                    ->iconButton()
                    ->modalHeading('Complete Booking')
                    ->modalDescription('Please enter the reference number for the booking.')
                    ->label('Complete Booking')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn($record) => $record->status === BookingStatus::Confirmed)
                    ->schema([
                        TextInput::make('ref_number')
                            ->label('Ref Number')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => BookingStatus::Completed,
                            'ref_number' => $data['ref_number'],
                        ]);
                    }),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
