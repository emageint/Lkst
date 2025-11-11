<?php

namespace App\Enums;

enum CourseAccreditingBody: string
{
    case LKST = 'LKST';
    case CITB = 'CITB';
    case IOSH = 'IOSH';
    case UKATA = 'UKATA';
    case IPAF = 'IPAF';
    case PASMA = 'PASMA';
    case NPORS = 'NPORS';

    public function label(): string
    {
        return $this->value; // labels are the same as values in this case
    }

    public static function options(): array
    {
        return [
            self::LKST->value => self::LKST->label(),
            self::CITB->value => self::CITB->label(),
            self::IOSH->value => self::IOSH->label(),
            self::UKATA->value => self::UKATA->label(),
            self::IPAF->value => self::IPAF->label(),
            self::PASMA->value => self::PASMA->label(),
            self::NPORS->value => self::NPORS->label(),
        ];
    }
}
