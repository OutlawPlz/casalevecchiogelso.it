<?php

namespace App\Enums;

enum CancellationPolicy: string
{
    case FLEXIBLE = 'flexible';
    case MODERATE = 'moderate';
    case STRICT = 'strict';

    const int FULL_REFUND = 1;

    const int NO_REFUND = 0;

    public function refundFactor(): float
    {
        return .7;
    }

    public function timeWindow(): string
    {
        return match ($this) {
            self::FLEXIBLE => '1 day',
            self::MODERATE => '5 days',
            self::STRICT => '14 days',
        };
    }
}
