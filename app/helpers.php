<?php

namespace App\Helpers;

use App\Models\Reservation;
use Carbon\Carbon;
use DateInterval;
use DateMalformedPeriodStringException;
use DatePeriod;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use IntlDateFormatter;
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

function get_overnight_stay(array $priceList): array
{
    $overnightStay = array_find($priceList, fn ($line) => is_overnight_stay($line['product']));

    if (! $overnightStay) throw new \RuntimeException('Product "overnight_stay" not found in price list.');

    return $overnightStay;
}

function refund_factor(Reservation $reservation, ?Carbon $date = null): float
{
    if (! $date) $date = now();

    $refundFactor = 1;

    if ($reservation->inProgress()) $refundFactor = 0;

    if ($date->isBetween(...$reservation->refundPeriod)) {
        $refundFactor = $reservation->cancellation_policy->refundFactor();
    }

    return $refundFactor;
}

function refund_amount(Reservation $reservation, ?Carbon $date = null, ?int $tot = null): int
{
    if (! $date) $date = now();

    $refundFactor = 1;

    if ($reservation->inProgress()) $refundFactor = 0;

    if ($date->isBetween(...$reservation->refundPeriod)) {
        $refundFactor = $reservation->cancellation_policy->refundFactor();
    }

    $tot ??= $reservation->tot;

     return $tot * $refundFactor;
}

function money_formatter(int $cents, ?string $currency = null): string
{
    $currency ??= config('services.stripe.currency');

    $formatter = new NumberFormatter(config('app.locale'), NumberFormatter::CURRENCY);

    return $formatter->formatCurrency($cents / 100, $currency);
}

/**
 * @throws \DateInvalidTimeZoneException
 */
function datetime_formatter(DateTimeInterface|int $datetime): string
{
    $formatter = new IntlDateFormatter(config('app.locale'), IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);

    if (is_int($datetime)) $datetime = new DateTime("@$datetime");

    return $formatter->format($datetime);
}
