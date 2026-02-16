<?php

use App\Actions\ApproveReservation;
use App\Actions\CancelReservation;
use App\Enums\ReservationStatus;
use App\Jobs\Refund;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;

test('reservation can be approved', function () {
    $reservation = Reservation::factory()->create(['status' => ReservationStatus::QUOTE]);

    (new ApproveReservation)($reservation);

    expect($reservation->status)
        ->toBe(ReservationStatus::PENDING)
        ->and($reservation->checkout_session)->toBeArray()
        ->and($reservation->checkout_session['url'])->toBeUrl();
});

test('reservation can be cancelled', function (Reservation $reservation) {
    Queue::fake();

    (new CancelReservation)($reservation, 'Because reasons', $reservation->user);

    expect($reservation->status)->toBe(ReservationStatus::CANCELLED);

    $refundAmount = (int) ($reservation->tot * .7);

    now()->isBetween(...$reservation->refundPeriod)
        ? Queue::assertPushed(Refund::class, fn (Refund $refund) => $refundAmount === $refund->cents)
        : Queue::assertNotPushed(Refund::class);

    $activity = Activity::query()->first();

    expect($activity->description)
        ->toBe("The {$reservation->user->role} cancelled the reservation.")
        ->and($activity->properties->toArray())->toMatchArray([
            'reservation' => $reservation->ulid,
            'user' => $reservation->user->email,
            'message' => 'Because reasons',
        ]);
})->with([
    fn () => Reservation::factory()->inRefundPeriod()->create(),
    fn () => Reservation::factory()->inProgress()->create(),
]);

test('reservation can be rejected', function () {
    $host = User::factory()->host()->create();

    $reservation = Reservation::factory()->create(['status' => ReservationStatus::QUOTE]);

    $this
        ->actingAs($host)
        ->post(route('reservation.reject', $reservation))
        ->assertRedirect();

    expect($reservation->fresh()->status)->toBe(ReservationStatus::REJECTED);

    $activity = Activity::query()->first();

    expect($activity->description)
        ->toBe('The host rejected the reservation.')
        ->and($activity->properties->toArray())->toMatchArray([
            'reservation' => $reservation->ulid,
            'user' => $host->email,
        ]);
});
