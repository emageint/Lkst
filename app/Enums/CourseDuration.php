<?php

namespace App\Enums;

enum CourseDuration: string
{
    case HALF_DAY = 'half_day';
    case ONE_DAY = '1_day';
    case MULTI_DAY = 'multi_day';
    case ONLINE = 'online';

    public function label(): string
    {
        return match ($this) {
            self::HALF_DAY => '½ day',
            self::ONE_DAY => '1 day',
            self::MULTI_DAY => 'Multi-day',
            self::ONLINE => 'Online',
        };
    }

    public static function options(): array
    {
        return [
            self::HALF_DAY->value => self::HALF_DAY->label(),
            self::ONE_DAY->value => self::ONE_DAY->label(),
            self::MULTI_DAY->value => self::MULTI_DAY->label(),
            self::ONLINE->value => self::ONLINE->label(),
        ];
    }
}
