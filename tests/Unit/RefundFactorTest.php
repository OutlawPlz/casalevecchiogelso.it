<?php

use App\Models\Reservation;
use App\Models\User;
use Database\Factories\ReservationFactory;
use Tests\TestCase;

use function App\Helpers\refund_factor;

uses(TestCase::class);

beforeEach(function () {
    ReservationFactory::dontExpandRelationshipsByDefault();
});

it('returns a full refund', function () {
    $reservation = Reservation::factory()->make();

    expect(refund_factor($reservation))->toBe(1.0);

    $host = User::factory()->host()->make();

    $reservation = Reservation::factory()->inProgress()->make();

    expect(refund_factor($reservation, causer: $host))->toBe(1.0);
});

it('returns a partial refund', function () {
    $reservation = Reservation::factory()->inRefundPeriod()->make();

    expect(refund_factor($reservation))->toBe(0.7);
});

it('returns a zero refund', function () {
    $reservation = Reservation::factory()->inProgress()->make();

    expect(refund_factor($reservation))->toBe(0.0);
});
