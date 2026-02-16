<?php

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\ReservationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new ReservationPolicy;

    $this->host = User::factory()->host()->create();
});

it('allows host to reject a reservation', function (ReservationStatus $status) {
    $reservation = Reservation::factory()->create(['status' => $status]);

    expect($this->policy->reject($this->host, $reservation))->toBeTrue();
})->with([
    ReservationStatus::QUOTE,
    ReservationStatus::PENDING,
]);

it('denies guest from rejecting a reservation', function () {
    $reservation = Reservation::factory()->create();

    expect($this->policy->reject($reservation->user, $reservation))->toBeFalse();
});

it('denies host from rejecting a reservation', function (ReservationStatus $status) {
    $reservation = Reservation::factory()->create(['status' => $status]);

    expect($this->policy->reject($this->host, $reservation))->toBeFalse();
})->with([
    ReservationStatus::CONFIRMED,
    ReservationStatus::CANCELLED,
    ReservationStatus::REJECTED,
    ReservationStatus::COMPLETED,
]);

it('allows host or owner to cancel a reservation', function (ReservationStatus $status) {
    $reservation = Reservation::factory()->create(['status' => $status]);

    expect($this->policy->cancel($this->host, $reservation))->toBeTrue()
        ->and($this->policy->cancel($reservation->user, $reservation))->toBeTrue();
})->with([
    ReservationStatus::CONFIRMED,
    ReservationStatus::PENDING,
]);

it('denies a stranger from cancelling a reservation', function () {
    $stranger = User::factory()->create();

    $reservation = Reservation::factory()->create();

    expect($this->policy->cancel($stranger, $reservation))->toBeFalse();
});

it('denies cancelling a reservation', function (ReservationStatus $status) {
    $reservation = Reservation::factory()->create(['status' => $status]);

    expect($this->policy->cancel($this->host, $reservation))->toBeFalse()
        ->and($this->policy->cancel($reservation->user, $reservation))->toBeFalse();
})->with([
    ReservationStatus::QUOTE,
    ReservationStatus::REJECTED,
    ReservationStatus::CANCELLED,
    ReservationStatus::COMPLETED,
]);

it('allows host to approve a reservation', function () {
    $reservation = Reservation::factory()->create(['status' => ReservationStatus::QUOTE]);

    expect($this->policy->approve($this->host, $reservation))->toBeTrue();
});

it('denies guest from approving a reservation', function () {
    $reservation = Reservation::factory()->create(['status' => ReservationStatus::QUOTE]);

    expect($this->policy->approve($reservation->user, $reservation))->toBeFalse();
});

it('denies host from approving a reservation', function (ReservationStatus $status) {
    $reservation = Reservation::factory()->create(['status' => $status]);

    expect($this->policy->approve($this->host, $reservation))->toBeFalse();
})->with([
    ReservationStatus::PENDING,
    ReservationStatus::CONFIRMED,
    ReservationStatus::CANCELLED,
    ReservationStatus::REJECTED,
    ReservationStatus::COMPLETED,
]);

it('allows host to view any reservation', function () {
    $reservation = Reservation::factory()->create();

    expect($this->policy->view($this->host, $reservation))->toBeTrue();
});

it('allows the owner to view their reservation', function () {
    $reservation = Reservation::factory()->create();

    expect($this->policy->view($reservation->user, $reservation))->toBeTrue();
});

it('denies a stranger from viewing a reservation', function () {
    $stranger = User::factory()->create();

    $reservation = Reservation::factory()->create();

    expect($this->policy->view($stranger, $reservation))->toBeFalse();
});
