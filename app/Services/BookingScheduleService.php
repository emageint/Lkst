<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BookingScheduleService
{
    private const WORK_START_HOUR = 8;
    private const WORK_END_HOUR = 18;

    public function calculateEnd(?CarbonInterface $start, ?int $durationHours): ?CarbonInterface
    {
        if (!$start || !$durationHours) {
            return null;
        }
 
        $workStartHour = self::WORK_START_HOUR;
        $workEndHour = self::WORK_END_HOUR;
        $remainingMinutes = $durationHours * 60;
        $current = $start->copy();

        if ($current->hour < $workStartHour) {
            $current = $current->copy()->setTime($workStartHour, 0);
        }

        if ($current->hour >= $workEndHour) {
            $current = $current->copy()->addDay()->setTime($workStartHour, 0);
        }

        $current = $this->moveToNextWorkingDay($current, $workStartHour);

        while ($remainingMinutes > 0) {
            if ($this->isNonWorkingDay($current)) {
                $current = $this->moveToNextWorkingDay($current, $workStartHour);
                continue;
            }

            $endOfDay = $current->copy()->setTime($workEndHour, 0);
            $availableMinutes = max(0, $current->diffInMinutes($endOfDay, false));

            if ($availableMinutes <= 0) {
                $current = $this->moveToNextWorkingDay($current->copy()->addDay()->setTime($workStartHour, 0), $workStartHour);
                continue;
            }

            if ($remainingMinutes <= $availableMinutes) {
                return $current->copy()->addMinutes($remainingMinutes);
            }

            $remainingMinutes -= $availableMinutes;
            $current = $this->moveToNextWorkingDay($current->copy()->addDay()->setTime($workStartHour, 0), $workStartHour);
        }

        return $current;
    }

    private function moveToNextWorkingDay(CarbonInterface $current, int $workStartHour): CarbonInterface
    {
        $next = $current->copy();

        while ($this->isNonWorkingDay($next)) {
            $next = $next->copy()->addDay()->setTime($workStartHour, 0);
        }

        return $next;
    }

    private function isNonWorkingDay(CarbonInterface $date): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        $holidayDates = $this->getUkBankHolidayDates();

        return in_array($date->toDateString(), $holidayDates, true);
    }

    private function getUkBankHolidayDates(): array
    {
        return Cache::remember('uk_bank_holidays', now()->addDay(), function (): array {
            $response = Http::get('https://www.gov.uk/bank-holidays.json');

            if (!$response->ok()) {
                return [];
            }

            $payload = $response->json();
            $events = $payload['england-and-wales']['events'] ?? [];

            return collect($events)
                ->pluck('date')
                ->filter()
                ->values()
                ->all();
        });
    }

    public function normalizeStart(mixed $value): ?CarbonInterface
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value;
        }

        return Carbon::parse($value);
    }
}

