<?php

namespace App\Helpers;

use App\Enums\CancellationPolicy;
use App\Models\Message;
use App\Models\Reservation;
use App\Models\User;
use Carbon\CarbonInterface;
use DateInterval;
use DateMalformedPeriodStringException;
use DatePeriod;
use DateTime;
use DateTimeInterface;
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
): array {
    if (is_string($interval)) {
        $interval = new DateInterval($interval);
    }

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

    if (! $overnightStay) {
        throw new \RuntimeException('Product "overnight_stay" not found in price list.');
    }

    return $overnightStay;
}

function refund_factor(Reservation $reservation, ?CarbonInterface $date = null, ?User $causer = null): float
{
    $date ??= now();

    $refundFactor = CancellationPolicy::FULL_REFUND;

    if ($causer?->isHost()) {
        return $refundFactor;
    }

    if ($reservation->inProgress()) {
        $refundFactor = CancellationPolicy::NO_REFUND;
    }

    if ($date->isBetween(...$reservation->refundPeriod)) {
        $refundFactor = $reservation->cancellation_policy->refundFactor();
    }

    return $refundFactor;
}

function money_format(int $cents, ?string $currency = null): string
{
    $currency ??= config('services.stripe.currency');

    $formatter = new NumberFormatter(config('app.locale'), NumberFormatter::CURRENCY);

    return $formatter->formatCurrency($cents / 100, $currency);
}

function date_format(
    DateTimeInterface|int $datetime,
    ?int $date = IntlDateFormatter::SHORT,
    ?int $time = IntlDateFormatter::SHORT
): string {
    $formatter = new IntlDateFormatter(
        config('app.locale'),
        $date ?? IntlDateFormatter::NONE,
        $time ?? IntlDateFormatter::NONE
    );

    if (is_int($datetime)) {
        $datetime = new DateTime("@$datetime");
    }

    return $formatter->format($datetime);
}

function is_template(Message $message): bool
{
    return str_starts_with($message->content['raw'], '/blade');
}
