<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Enums\BookingStatus;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.name')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start')
                    ->label('Date')
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d/m/Y H:i') : '-')
                    ->sortable(),

                TextColumn::make('instructor.full_name')
                    ->label('Instructor')
                    ->searchable([ 'first_name', 'last_name' ]),

                TextColumn::make('delegates_count')
                    ->label('Delegates')
                    ->counts('delegates')
                    ->formatStateUsing(fn($state, $record) => $state . ' / ' . ($record->max_delegates ?? '∞')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->defaultSort('start', 'desc')
            ->recordActions([]);
    }
}
