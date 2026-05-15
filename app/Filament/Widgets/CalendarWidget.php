<?php

namespace App\Filament\Widgets;

use App\Actions\HolidaysAction;
use App\Enums\HolidaysStatus;
use App\Models\Booking;
use App\Models\Course;
use App\Models\CourseVariable;
use App\Models\Holiday;
use App\Models\User;
use App\Models\ExternalCalendarAccount;
use App\Services\BookingScheduleService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;


class CalendarWidget extends FullCalendarWidget
{

    use HasWidgetShield;

    public Model|string|null $model = Booking::class;

    public function fetchEvents(array $info): array
    {
        $ukHolidays = HolidaysAction::ukHolidaysWithName();
        $start = Carbon::parse($info['start']);
        $end = Carbon::parse($info['end']);

        $query = Booking::query()
            ->with([ 'course', 'customer', 'instructor' ])
            ->whereNotNull('start')
            ->whereNotNull('end')
            ->where('status', '!=', \App\Enums\BookingStatus::Expired)
            ->where(function ($query) use ($start, $end) {
                $query->where('start', '<', $end)
                    ->where('end', '>', $start);
            })
            ->when(
                auth()->user()?->hasRole('Instructor'),
                fn($query) => $query->where('instructor_id', auth()->id())
            );

        $bookings = $query->get()
            ->map(function (Booking $booking) {
                $color = $booking->instructor?->color ?? '#3b82f6';

                return [
                    'id' => (string)$booking->id,
                    'title' => $booking->instructor?->full_name ?? 'Booking',
                    'start' => $booking->start?->toIso8601String(),
                    'end' => $booking->end?->toIso8601String(),
                    'allDay' => false,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => $this->getTextColorForBackground($color),
                    'extendedProps' => [
                        'course' => $booking->course?->name ?? '',
                        'location' => $booking->location_lkst_yard
                            ? 'LKST Yard'
                            : $booking->training_location,
                    ],
                ];
            })
            ->values()
            ->all();

        return array_merge($bookings, $ukHolidays);
    }


    public function eventDidMount(): string
    {
        return <<<JS
            function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
                el.setAttribute("x-tooltip", "tooltip");
                el.setAttribute("data-tippy-allowHTML", "true");
                var loc = 'Location: ' + (event.extendedProps.location || 'N/A');
                var parts = [event.extendedProps.course, loc].filter(Boolean);
                el.setAttribute("x-data", "{ tooltip: '" + parts.join('<br>') + "' }");
            }
        JS;
    }


    protected function getTextColorForBackground(string $hex): string
    {
        $hex = ltrim($hex, '#');
        [ $r, $g, $b ] = array_map(fn($c) => hexdec($c) / 255, str_split($hex, 2));

        $toLinear = fn(float $c): float => $c <= 0.04045
            ? $c / 12.92
            : (($c + 0.055) / 1.055) ** 2.4;

        $luminance = 0.2126 * $toLinear($r) + 0.7152 * $toLinear($g) + 0.0722 * $toLinear($b);

        return $luminance > 0.179 ? '#1f2937' : '#ffffff';
    }

    protected function headerActions(): array
    {
        return [
            Action::make('connectOutlook')
                ->label('Connect Outlook')
                ->url(route('outlook.connect'))
                ->visible(fn() => $this->canConnectOutlook())
                ->openUrlInNewTab(),
            Actions\CreateAction::make()
                ->extraAttributes([ 'style' => 'display:none' ])
                ->mountUsing(function (Schema $form, array $arguments): void {
                    $start = data_get($arguments, 'start');
                    if (!$start) {
                        return;
                    }

                    $start = $start instanceof Carbon
                        ? $start->copy()
                        : Carbon::parse($start);

                    $form->fill([
                        'start' => $start->setTime(8, 0),
                    ]);
                })

        ];


    }


    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => $this->canManageCalendar()),
            Actions\DeleteAction::make()
                ->visible(fn() => $this->canManageCalendar()),
        ];
    }


    protected function viewAction(): Action
    {
        return Actions\ViewAction::make();
    }

    protected function canManageCalendar(): bool
    {
        $user = auth()->user();

        return $user?->hasRole([ 'super_admin', 'Administrator' ]) ?? false;
    }

    protected function canConnectOutlook(): bool
    {
        $user = auth()->user();

        if (!$user?->hasRole('Instructor')) {
            return false;
        }

        return !ExternalCalendarAccount::query()
            ->where('user_id', $user->id)
            ->where('provider', 'outlook')
            ->exists();
    }


    public function getFormSchema(): array
    {
        return [
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
                    Toggle::make('location_lkst_yard')
                        ->label('Location: LKST Yard')
                        ->inline(false)
                        ->default(false)
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
                                            $set('end', $end);
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
                                $set('end', $end);
                            }
                        }),
                    DateTimePicker::make('end')
                        ->label('End')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y H:i')
                        ->seconds(false),
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

                    RichEditor::make('price')
                        ->label('Price + VAT')
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('po_number')
                        ->label('PO Number')
                        ->maxLength(255)
                        ->columnSpan(1)
                        ->hiddenOn('create'),

                ]),
        ];
    }
}
