<?php

namespace App\Helpers;

use App\Models\Reservation;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeInterface;
use NumberFormatter;

/**
 * @param DateTimeInterface|DateInterval|DatePeriod $date
 * @return string
 */
function iso8601_encode(DateTimeInterface|DateInterval|DatePeriod $date): string
{
    if ($date instanceof DateTimeInterface) {
        return $date->format(DateTimeInterface::ATOM);
    }

    $iso8601 = '';

    if ($date instanceof DatePeriod) {
        $iso8601 .= "R$date->recurrences/";

        $iso8601 .= $date->getStartDate()->format(DateTimeInterface::ATOM);

        $date = $date->getDateInterval();
    }

    if ($date instanceof DateInterval) {
        if ($iso8601) $iso8601 .= '/';

        $P = $date->invert ? '-P' : 'P';

        foreach (['y', 'm', 'd'] as $period) {
            if ($date->$period) $P .= $date->$period . strtoupper($period);
        }

        $T = '';

        foreach (['h', 'i', 's'] as $time) {
            if ($date->$time) $T .= $date->$time . strtoupper($time);
        }

        if ($T) $T = 'T' . $T;

        $iso8601 .= $P . $T;
    }

    return $iso8601;
}

/**
 * @param string|null $iso8601
 * @return DateTimeInterface|DateInterval|DatePeriod|null
 * @throws \Exception
 */
function iso8601_decode(?string $iso8601): DateTimeInterface|DateInterval|DatePeriod|null
{
    if (! $iso8601) return null;

    $date = null;
    $interval = null;
    $recurrences = null;

    $segments = explode('/', $iso8601);

    foreach ($segments as $segment) {
        $firstLetter = substr($segment, 0, 1);

        match ($firstLetter) {
            'P' => $interval = new DateInterval($segment),
            'R' => $recurrences = (int) substr($segment, 1, null),
            default => $date = new DateTime($segment)
        };
    }

    if ($recurrences) {
        return new DatePeriod($date, $interval, $recurrences);
    }

    return $interval ?? $date;
}

/**
 * @param DateTimeInterface $start
 * @param DateTimeInterface $end
 * @param string|DateInterval $interval
 * @param string $format
 * @return string[]
 * @throws \Exception
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

/**
 * @param  string  $stripeId
 * @return bool
 */
function is_overnight_stay(string $stripeId): bool
{
    return config('reservation.overnight_stay') === $stripeId;
}

/**
 * @param Reservation $reservation
 * @param Carbon|null $date
 * @return int
 */
function refund_amount(Reservation $reservation, Carbon $date = null): int
{
    if (! $date) $date = now();

    $refundFactor = 1;

    if ($date->isAfter($reservation->check_in)) $refundFactor = 0;

    if ($date->isBetween(...$reservation->refundPeriod)) {
        $refundFactor = $reservation->cancellation_policy->refundFactor();
    }

     return $reservation->tot * $refundFactor;
}

/**
 * @param int $cents
 * @param string $currency
 * @return string
 */
function money_formatter(int $cents, string $currency = 'eur'): string
{
    $formatter = new NumberFormatter(config('app.locale'), NumberFormatter::CURRENCY);

    return $formatter->formatCurrency($cents / 100, $currency);
}
