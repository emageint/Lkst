<?php

namespace App\Filament\Resources\Courses;

use App\Enums\CourseAccreditingBody;
use App\Filament\Resources\Courses\Pages\ManageCourses;
use App\Models\Course;
use BackedEnum;
use App\Enums\CourseDuration;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CourseResource extends Resource
{

    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Courses';
    protected static ?string $modelLabel = 'Course';
    protected static ?int $navigationSort = 30; // after Tutors (20)

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Course name')
                    ->required()
                    ->maxLength(255),

                Select::make('accrediting_body')
                    ->label('Accrediting body')
                    ->options(CourseAccreditingBody::options())
                    ->required(),

                Textarea::make('description')
                    ->label('Course description (short summary)')
                    ->rows(4),
                Select::make('duration')
                    ->label('Duration')
                    ->options(CourseDuration::options())
                    ->required(),

                TextInput::make('validity_period')
                    ->label('Validity period (months)')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Course name')->sortable()->searchable(),
                TextColumn::make('duration')
                    ->label('Course Duration')
                    ->formatStateUsing(function ($state) {

                        if ($state instanceof \App\Enums\CourseDuration) {
                            return $state->label();
                        }
                        return \App\Enums\CourseDuration::tryFrom((string)$state)?->label() ?? (string)$state;
                    })
                    ->sortable(),
                TextColumn::make('validity_period')->label('Validity Period')->sortable(),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCourses::route('/'),
        ];
    }
}
