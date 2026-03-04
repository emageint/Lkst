<?php

namespace App\Actions;

use App\Enums\HolidaysStatus;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Cache;


class HolidaysAction
{
    /**
     * Връща масив от Carbon обекти за UK bank holidays
     * Кешира за 1 ден
     */
    public static function ukHolidays(): array
    {
        return Cache::remember('uk_holidays', now()->addDay(), function () {
            $url = "https://www.gov.uk/bank-holidays.json";
            $json = json_decode(file_get_contents($url), true);

            return collect($json['england-and-wales']['events'])
                ->pluck('date')
                ->map(fn($date) => Carbon::parse($date)->startOfDay()) // Carbon обект
                ->toArray();
        });
    }

    /**
     * Връща масив с UK holidays с информация за име, цвят и дата
     */
    public static function ukHolidaysWithName(): array
    {
        return collect(self::ukHolidays())->map(function ($date) {
            return [
                'id' => 'bank_holiday_' . $date->toDateString(),
                'title' => 'Bank Holiday',
                'start' => $date,
                'end' => $date,
                'color' => 'purple',
                'allDay' => true,
                'url' => '#',
                'display' => 'list-item',
            ];
        })->toArray();
    }


}
