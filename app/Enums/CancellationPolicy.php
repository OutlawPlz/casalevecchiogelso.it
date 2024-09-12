<?php

namespace App\Enums;

enum CancellationPolicy: string
{
    case FLEXIBLE = 'flexible';
    case MODERATE = 'moderate';
    case STRICT = 'strict';

    /**
     * @return float
     */
    public function refundFactor(): float
    {
        return match ($this) {
            self::FLEXIBLE => .3,
            self::MODERATE, self::STRICT => .5,
        };
    }

    /**
     * @return string
     */
    public function timeWindow(): string
    {
        return match ($this) {
            self::FLEXIBLE => '1 day',
            self::MODERATE => '7 days',
            self::STRICT => '1 month',
        };
    }
}
