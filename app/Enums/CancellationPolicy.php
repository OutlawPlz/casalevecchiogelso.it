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
        return .3;
    }

    /**
     * @return string
     */
    public function timeWindow(): string
    {
        return match ($this) {
            self::FLEXIBLE => '1 day',
            self::MODERATE => '5 days',
            self::STRICT => '14 days',
        };
    }
}
