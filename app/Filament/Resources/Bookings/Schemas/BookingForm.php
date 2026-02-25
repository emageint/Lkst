<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Course;
use App\Models\CourseVariable;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Services\BookingScheduleService;
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
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('course_variable_type', null);
                                $set('course_duration', null);
                                $set('max_delegates', null);
                                $set('instructor_id', null);
                            })
                            ->columnSpan(1),


                        Section::make('')
                            ->columns(3)
                            ->schema([
                                Select::make('course_variable_type')
                                    ->label('Course Type')
                                    ->options(fn(Get $get) => filled($get('course_id'))
                                        ? CourseVariable::where('course_id', $get('course_id'))
                                            ->pluck('type', 'type')
                                        : []
                                    )
                                    ->searchable()
                                    ->required()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (!$state || blank($get('course_id'))) {
                                            $set('course_duration', null);
                                            $set('max_delegates', null);
                                            return;
                                        }

                                        $cv = CourseVariable::where('course_id', $get('course_id'))
                                            ->where('type', $state)
                                            ->first();
                                        if ($cv) {
                                            $set('course_duration', $cv->course_duration);
                                            $set('max_delegates', $cv->max_delegates);
                                            $service = app(BookingScheduleService::class);
                                            $start = $service->normalizeStart($get('start'));
                                            $end = $service->calculateEnd($start, (int)$cv->course_duration);
                                            if ($end) {
                                                $set('end', $end->toDateTimeString());
                                            }
                                        }
                                    })
                                    ->columnSpan(1),


                                TextEntry::make('course_duration_display')
                                    ->label('Course Duration (hours)')
                                    ->state(fn(Get $get) => filled($get('course_duration')) ? ((string)$get('course_duration') . ' h') : '—')
                                    ->columnSpan(1),

                                TextEntry::make('max_delegates_display')
                                    ->label('Max Delegates')
                                    ->state(fn(Get $get) => filled($get('max_delegates')) ? (string)$get('max_delegates') : '—')
                                    ->columnSpan(1),
                            ])
                            ->columnSpanFull(),

                        Hidden::make('course_duration'),
                        Hidden::make('max_delegates'),

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
                                modifyQueryUsing: fn($query, Get $get) => $query
                                    ->whereHas('roles', fn($q) => $q->where('name', 'Instructor'))
                                    ->when(
                                        filled($get('course_id')),
                                        fn($q) => $q->whereHas('courses', fn($cq) => $cq->where('courses.id', $get('course_id')))
                                    )
                                    ->when(
                                        filled($get('start')),
                                        fn($q) => $q->whereDoesntHave('holidays', function ($hq) use ($get) {
                                            $date = \Carbon\Carbon::parse($get('start'))->toDateString();
                                            $hq->where('start_date', '<=', $date)
                                                ->where('end_date', '>=', $date);
                                        })
                                    )
                            )
                            ->getOptionLabelFromRecordUsing(fn(User $record) => $record->first_name . ' ' . $record->last_name)
                            ->searchable([ 'first_name', 'last_name', 'email' ])
                            ->preload()
                            ->live()
                            ->disabled(fn(Get $get) => blank($get('course_id')))
                            ->columnSpan(1),
                        DateTimePicker::make('start')
                            ->label('Start')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $set('instructor_id', null);
                                $service = app(BookingScheduleService::class);
                                $start = $service->normalizeStart($state);
                                $end = $service->calculateEnd($start, (int)$get('course_duration'));
                                if ($end) {
                                    $set('end', $end->toDateTimeString());
                                }
                            })
                            ->dehydrateStateUsing(fn($state) => $state
                                ? \Carbon\Carbon::parse($state)->toDateTimeString()
                                : $state),


                        DateTimePicker::make('end')
                            ->label('End')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->dehydrateStateUsing(fn($state) => $state
                                ? \Carbon\Carbon::parse($state)->toDateTimeString()
                                : $state),

                    ])
            ]);
    }
}

