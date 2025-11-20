<?php

namespace App\Filament\Resources\Learners\Pages;

use App\Enums\CourseStatus;
use App\Filament\Resources\Learners\LearnerResource;
use App\Livewire\LearnerCoursesStats;
use App\Models\Course;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ManageLeanerCourses extends ManageRelatedRecords
{
    protected static string $resource = LearnerResource::class;

    protected static string $relationship = 'courses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function mount($record): void
    {
        parent::mount($record);

        \Filament\Facades\Filament::registerRenderHook(
            'widgets.stats-overview.before',
            fn() => ""
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date_completed')
                    ->label('Date Completed')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->displayFormat('d/m/Y')
                    ->required(),

                FileUpload::make('certificate_path')
                    ->label('Attach Certificate')
                    ->disk('public')
                    ->directory('certificates')
                    ->visibility('public')
                    ->downloadable()
                    ->openable()
                    ->imagePreviewHeight('150')
                    ->required(false),

                Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();
        return 'Manage ' . $record?->full_name . ' Courses' ?? 'Manage Learner Courses';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Course / Qualification')->searchable()->sortable(),
                TextColumn::make('pivot.date_completed')
                    ->label('Date Completed')
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-'),
                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->state(function ($record) {
                        $completed = $record->pivot?->date_completed;
                        $months = (int)($record->validity_period ?? 0);
                        if (!$completed || $months <= 0) return null;
                        return Carbon::parse($completed)->addMonths($months)->format('d/m/Y');
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(function ($record) {
                        $completed = $record->pivot?->date_completed;
                        $months = (int)($record->validity_period ?? 0);
                        return CourseStatus::fromDates($completed, $months)->label();
                    })
                    ->badge()
                    ->color(function ($record): string {
                        $completed = $record->pivot?->date_completed;
                        $months = (int)($record->validity_period ?? 0);
                        return CourseStatus::fromDates($completed, $months)->color();
                    }),
                IconColumn::make('pivot.certificate_path')
                    ->label('Certificate')
                    ->icon(fn($record) => $record->pivot?->certificate_path ? 'heroicon-o-arrow-down-tray' : 'heroicon-o-x-mark')
                    ->color(fn($record) => $record->pivot?->certificate_path ? 'success' : 'gray')
                    ->tooltip(fn($record) => $record->pivot?->certificate_path ? 'Download Certificate' : 'No Certificate')
                    ->url(fn($record) => $record->pivot?->certificate_path
                        ? Storage::disk('public')->url($record->pivot->certificate_path)
                        : null,
                        shouldOpenInNewTab: true
                    ),
                TextColumn::make('pivot.notes')
                    ->label('Notes')
                    ->wrap()
                    ->limit(50),
            ])
            ->filters([

            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach Course')
                    ->recordSelectSearchColumns([ 'name' ])
                    ->schema([
                        Select::make('recordId')
                            ->label('Course')
                            ->options(function (ManageLeanerCourses $livewire) {
                                $learner = $livewire->getOwnerRecord();

                                $attachedIds = $learner->courses()
                                    ->select('courses.id')
                                    ->pluck('courses.id');

                                return Course::query()
                                    ->whereNotIn('id', $attachedIds)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('date_completed')
                            ->label('Date Completed')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->displayFormat('d/m/Y')
                            ->required(),

                        FileUpload::make('certificate_path')
                            ->label('Attach Certificate')
                            ->disk('public')
                            ->directory('certificates')
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->imagePreviewHeight('150')
                            ->imageEditor()
                            ->required(false),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3),
                    ]),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DetachAction::make()->iconButton()->icon('heroicon-o-trash'),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LearnerCoursesStats::class,
        ];
    }

}
