<?php

use App\Models\Reservation;
use App\Models\User;
use Database\Factories\ReservationFactory;
use Tests\TestCase;

use function App\Helpers\date_format;
use function App\Helpers\dates_in_range;
use function App\Helpers\get_overnight_stay;
use function App\Helpers\is_overnight_stay;
use function App\Helpers\money_format;
use function App\Helpers\refund_factor;

uses(TestCase::class);

it('returns dates between two dates', function () {
    $dates = dates_in_range(new DateTime('2025-06-01'), new DateTime('2025-06-05'));

    expect($dates)->toBe(['2025-06-02', '2025-06-03', '2025-06-04', '2025-06-05']);
});

it('detects an overnight stay product', function () {
    expect(is_overnight_stay('prod_QFGF5ANGoEMpOI'))
        ->toBeTrue()
        ->and(is_overnight_stay('prod_OTHER'))
        ->toBeFalse();

});

it('returns the overnight stay line', function () {
    $priceList = [
        ['product' => 'prod_OTHER', 'name' => 'Cleaning', 'unit_amount' => 5000, 'quantity' => 1],
        ['product' => 'prod_QFGF5ANGoEMpOI', 'name' => 'Overnight stay', 'unit_amount' => 25000, 'quantity' => 7],
    ];

    expect(get_overnight_stay($priceList)['product'])
        ->toBe('prod_QFGF5ANGoEMpOI')
        ->and(fn() => get_overnight_stay([['product' => 'prod_OTHER']]))
        ->toThrow(RuntimeException::class);
});

it('formats cents as currency', function () {
    expect(money_format(100))
        ->toBe("1,00\u{00A0}€")
        ->and(money_format(175000))
        ->toBe("1.750,00\u{00A0}€");
});

it('formats a datetime', function () {
    expect(date_format(new DateTime('2025-06-15 14:30:00')))
        ->toBe('15/06/25, 14:30');
});

it('formats a date without time', function () {
    expect(date_format(new DateTime('2025-06-15'), IntlDateFormatter::SHORT, null))
        ->toBe('15/06/25');
});

it('returns a full refund', function () {
    ReservationFactory::dontExpandRelationshipsByDefault();

    $reservation = Reservation::factory()->make();

    expect(refund_factor($reservation))->toBe(1.0);

    $host = User::factory()->host()->make();

    $reservation = Reservation::factory()->inProgress()->make();

    expect(refund_factor($reservation, causer: $host))->toBe(1.0);
});

it('returns a partial refund', function () {
    ReservationFactory::dontExpandRelationshipsByDefault();

    $reservation = Reservation::factory()->inRefundPeriod()->make();

    expect(refund_factor($reservation))->toBe(0.7);
});

it('returns a zero refund', function () {
    ReservationFactory::dontExpandRelationshipsByDefault();

    $reservation = Reservation::factory()->inProgress()->make();

    expect(refund_factor($reservation))->toBe(0.0);
});
