<?php

use App\Models\Reservation;
use App\View\Components\ReservationFeed;

test('renders with the correct feed url', function () {
    $reservation = Reservation::factory()->create();

    $this->component(ReservationFeed::class, ['reservation' => $reservation])
        ->assertSee(route('reservation.feed', $reservation));
});
