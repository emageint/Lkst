<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Enums\CourseStatus;


class LearnerCoursesStats extends StatsOverviewWidget
{
    public $record; // ще получим Learner (User)

    protected function getStats(): array
    {
        $courses = $this->record->courses;

        $total = $courses->count();

        $dueSoon = $courses->filter(function ($course) {
            return CourseStatus::fromDates(
                    $course->pivot->date_completed,
                    $course->validity_period
                )->value === CourseStatus::DueSoon->value;
        })->count();

        $expired = $courses->filter(function ($course) {
            return CourseStatus::fromDates(
                    $course->pivot->date_completed,
                    $course->validity_period
                )->value === CourseStatus::Expired->value;
        })->count();

        return [
            Stat::make('Courses / Qualifications', $total)
                ->description('Total active courses')
                ->color('gray'),

            Stat::make('Due Soon', $dueSoon)
                ->description('Expiring soon')
                ->color('warning'),

            Stat::make('Expired', $expired)
                ->description('Already expired')
                ->color('danger'),
        ];
    }
}
