<?php

namespace App\Filament\Resources\Instructors\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\User;


class InstructorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(User::query()->whereHas('roles', function ($query) {
                $query->where('name', 'Instructor');
            }))
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->sortable([ 'first_name', 'last_name' ])
                    ->searchable([ 'first_name', 'last_name' ]),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),

                ColorColumn::make('color')
                    ->label('Colour'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }
}
