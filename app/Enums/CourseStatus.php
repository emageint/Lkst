<?php

namespace App\Enums;

use Carbon\Carbon;

enum CourseStatus: string
{
    case Valid = 'valid';
    case DueSoon = 'due_soon';
    case Expired = 'expired';
    case NA = 'na';

    public function label(): string
    {
        return match ($this) {
            self::Valid => 'Valid',
            self::DueSoon => 'Due Soon',
            self::Expired => 'Expired',
            self::NA => 'N/A',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Valid => 'success',
            self::DueSoon => 'warning',
            self::Expired => 'danger',
            self::NA => 'gray',
        };
    }

    public static function fromDates(?string $dateCompleted, ?int $validityMonths): self
    {
        if (!$dateCompleted || ($validityMonths ?? 0) <= 0) {
            return self::NA;
        }

        $expiry = Carbon::parse($dateCompleted)->addMonths((int)$validityMonths);
        $now = Carbon::now();

        if ($expiry->isPast()) {
            return self::Expired;
        }

        $monthsToExpiry = $now->diffInMonths($expiry, false);
        if ($monthsToExpiry <= 3) {
            return self::DueSoon;
        }

        return self::Valid;
    }
}
