<?php

namespace App\Helpers;

use App\Models\Reservation;
use Carbon\Carbon;
use DateInterval;
use DateMalformedPeriodStringException;
use DatePeriod;
use DateTimeInterface;
use Exception;
use NumberFormatter;

/**
 * @throws DateMalformedPeriodStringException
 * @throws Exception
 */
function dates_in_range(
    DateTimeInterface $start,
    DateTimeInterface $end,
    string|DateInterval $interval = 'P1D',
    string $format = 'Y-m-d'
): array
{
    if (is_string($interval)) $interval = new DateInterval($interval);

    $period = new DatePeriod($start, $interval, $end, DatePeriod::EXCLUDE_START_DATE | DatePeriod::INCLUDE_END_DATE);

    $dates = [];

    foreach ($period as $date) {
        $dates[] = $date->format($format);
    }

    return $dates;
}

function is_overnight_stay(string $stripeId): bool
{
    return config('reservation.overnight_stay') === $stripeId;
}

function refund_amount(Reservation $reservation, ?Carbon $date = null): int
{
    if (! $date) $date = now();

    $refundFactor = 1;

    if ($date->isAfter($reservation->check_in)) $refundFactor = 0;

    if ($date->isBetween(...$reservation->refundPeriod)) {
        $refundFactor = $reservation->cancellation_policy->refundFactor();
    }

     return $reservation->tot * $refundFactor;
}

function money_formatter(int $cents, string $currency = 'eur'): string
{
    $formatter = new NumberFormatter(config('app.locale'), NumberFormatter::CURRENCY);

    return $formatter->formatCurrency($cents / 100, $currency);
}
