<?php

namespace App\Filament\Resources\Courses;

use App\Enums\CourseAccreditingBody;
use App\Filament\Resources\Courses\Pages\ManageCourses;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;


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

                Select::make('course_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('accrediting_body')
                    ->label('Accrediting body')
                    ->options(CourseAccreditingBody::options())
                    ->required(),

                // Duration removed from form

                TextInput::make('validity_period')
                    ->label('Validity period (months)')
                    ->numeric()
                    ->minValue(0)
                    ->required(),


                Repeater::make('variables')
                    ->label('Course Variables')
                    ->relationship('variables')
                    ->defaultItems(1)
                    ->minItems(1)
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'Novice' => 'Novice',
                                'Experienced' => 'Experienced',
                                'Renewal' => 'Renewal',
                                'Fixed' => 'Fixed',
                            ])
                            ->required(),

                        TextInput::make('course_duration')
                            ->label('Course Duration (hours)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        TextInput::make('max_delegates')
                            ->label('Max Delegates')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ]),

                Textarea::make('description')
                    ->label('Course description (short summary)')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Course name')->sortable()->searchable(),
                TextColumn::make('accrediting_body')->label('Accrediting Body')->sortable()->searchable(),
                TextColumn::make('category.name')->label('Category')->sortable(),
                // Duration column removed from table
                TextColumn::make('validity_period')->label('Validity Period')->sortable(),
            ])
            ->filters([
                SelectFilter::make('course_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
//                TernaryFilter::make('variables')
//                    ->label('Variables')
//                    ->trueLabel('With variables')
//                    ->falseLabel('Without variables')
//                    ->queries(
//                        true: fn(Builder $query) => $query->whereHas('variables'),
//                        false: fn(Builder $query) => $query->whereDoesntHave('variables'),
//                        blank: fn(Builder $query) => $query,
//                    ),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                ReplicateAction::make()
                    ->iconButton()
                    ->using(function (Course $record) {
                        $replica = $record->replicate();
                        $replica->save();

                        foreach ($record->variables as $variable) {
                            $newVariable = $variable->replicate();
                            $newVariable->course_id = $replica->id;
                            $newVariable->save();
                        }

                        return $replica;
                    }),
                DeleteAction::make()->iconButton(),

            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCourses::route('/'),
        ];
    }
}

