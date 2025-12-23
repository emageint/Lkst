<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Course;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([

                        Select::make('course_id')
                            ->label('Course Name')
                            ->options(Course::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),


                        Select::make('customer_id')
                            ->label('Customer Name')
                            ->relationship(
                                name: 'customer',
                                titleAttribute: 'first_name',
                                modifyQueryUsing: fn($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'Customer'))
                            )
                            ->getOptionLabelFromRecordUsing(fn(User $record) => $record->first_name . ' ' . $record->last_name)
                            ->createOptionForm([
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
                            ])
                            ->createOptionUsing(function (array $data) {

                                $password = Str::password(12);
                                $user = User::create([
                                    'first_name' => $data['first_name'],
                                    'last_name' => $data['last_name'],
                                    'email' => $data['email'],
                                    'password' => bcrypt($password),
                                ]);
                                $user->assignRole('Customer');
                                return $user->id;
                            })
                            ->searchable([ 'first_name', 'last_name', 'email' ])
                            ->preload()
                            ->required()
                            ->columnSpan(1),


                        Select::make('instructor_id')
                            ->label('Instructor')
                            ->relationship(
                                name: 'instructor',
                                titleAttribute: 'first_name',
                                modifyQueryUsing: fn($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'Instructor'))
                            )
                            ->getOptionLabelFromRecordUsing(fn(User $record) => $record->first_name . ' ' . $record->last_name)
                            ->searchable([ 'first_name', 'last_name', 'email' ])
                            ->preload()
                            ->columnSpan(1),
                        DateTimePicker::make('job_datetime')->label('Date')->required()->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false),
                     
                    ])
            ]);
    }
}
