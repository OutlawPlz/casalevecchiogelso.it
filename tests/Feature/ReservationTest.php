<?php

use App\Actions\ApproveRequest;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;

test('reservation can be pre-approved', function () {
    $user = User::factory()->create();

    /** @var Reservation $reservation */
    $reservation = Reservation::factory()
        ->for($user)
        ->create(['status' => ReservationStatus::QUOTE]);

    (new ApproveRequest)($reservation);

    expect($reservation->status)
        ->toBe(ReservationStatus::PENDING)
        ->and($reservation->checkout_session)->toBeArray()
        ->and($reservation->checkout_session['url'])->toBeUrl();
});
