<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class BookingScheduleService
{
    public function calculateEnd(?CarbonInterface $start, ?int $durationHours): ?CarbonInterface
    {
        if (!$start || !$durationHours) {
            return null;
        }

        if ($durationHours <= 8) {
            return $start->copy()->addHours($durationHours);
        }

        return $start->copy()
            ->addDay()
            ->addHours($durationHours - 8);
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

