<?php

use App\Enums\ChangeRequestStatus;
use App\Enums\ReservationStatus;
use App\Models\ChangeRequest;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\ChangeRequestPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new ChangeRequestPolicy;

    $this->host = User::factory()->host()->create();
});

it('allows host to create a change request', function (ReservationStatus $status) {
    $reservation = Reservation::factory()->create(['status' => $status]);

    $changeRequest = ChangeRequest::factory()->make(['reservation_id' => $reservation->id]);

    expect($this->policy->create($this->host, $changeRequest))->toBeTrue();
})->with([
    ReservationStatus::QUOTE,
    ReservationStatus::PENDING,
    ReservationStatus::CONFIRMED,
]);

it('allows reservation owner to create a change request', function (ReservationStatus $status) {
    $reservation = Reservation::factory()->create(['status' => $status]);

    $changeRequest = ChangeRequest::factory()->make(['reservation_id' => $reservation->id]);

    expect($this->policy->create($reservation->user, $changeRequest))->toBeTrue();
})->with([
    ReservationStatus::QUOTE,
    ReservationStatus::PENDING,
    ReservationStatus::CONFIRMED,
]);

it('denies creating a change request when reservation is in a forbidden status', function (ReservationStatus $status) {
    $reservation = Reservation::factory()->create(['status' => $status]);

    $changeRequest = ChangeRequest::factory()->make(['reservation_id' => $reservation->id]);

    expect($this->policy->create($this->host, $changeRequest))->toBeFalse()
        ->and($this->policy->create($reservation->user, $changeRequest))->toBeFalse();
})->with([
    ReservationStatus::CANCELLED,
    ReservationStatus::REJECTED,
    ReservationStatus::COMPLETED,
]);

it('denies a stranger from creating a change request', function () {
    $stranger = User::factory()->create();

    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->make(['reservation_id' => $reservation->id]);

    expect($this->policy->create($stranger, $changeRequest))->toBeFalse();
});

it('allows host to view any change request', function () {
    $changeRequest = ChangeRequest::factory()->create();

    expect($this->policy->view($this->host, $changeRequest))->toBeTrue();
});

it('allows reservation owner to view their change request', function () {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create(['reservation_id' => $reservation->id]);

    expect($this->policy->view($reservation->user, $changeRequest))->toBeTrue();
});

it('denies a stranger from viewing a change request', function () {
    $stranger = User::factory()->create();

    $changeRequest = ChangeRequest::factory()->create();

    expect($this->policy->view($stranger, $changeRequest))->toBeFalse();
});

it('allows reservation owner to approve a change request created by the host', function () {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $this->host->id,
        'status' => ChangeRequestStatus::PENDING,
    ]);

    expect($this->policy->approve($reservation->user, $changeRequest))->toBeTrue();
});

it('allows host to approve a change request created by the guest', function () {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $reservation->user_id,
        'status' => ChangeRequestStatus::PENDING,
    ]);

    expect($this->policy->approve($this->host, $changeRequest))->toBeTrue();
});

it('denies approving a change request that is not pending', function (ChangeRequestStatus $status) {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $this->host->id,
        'status' => $status,
    ]);

    expect($this->policy->approve($reservation->user, $changeRequest))->toBeFalse();
})->with([
    ChangeRequestStatus::APPROVED,
    ChangeRequestStatus::REJECTED,
    ChangeRequestStatus::CANCELLED,
    ChangeRequestStatus::EXPIRED,
]);

it('denies host from approving a change request they created', function () {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $this->host->id,
        'status' => ChangeRequestStatus::PENDING,
    ]);

    expect($this->policy->approve($this->host, $changeRequest))->toBeFalse();
});

it('allows reservation owner to reject a change request created by the host', function () {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $this->host->id,
        'status' => ChangeRequestStatus::PENDING,
    ]);

    expect($this->policy->reject($reservation->user, $changeRequest))->toBeTrue();
});

it('allows host to reject a change request created by the guest', function () {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $reservation->user_id,
        'status' => ChangeRequestStatus::PENDING,
    ]);

    expect($this->policy->reject($this->host, $changeRequest))->toBeTrue();
});

it('denies rejecting a change request that is not pending', function (ChangeRequestStatus $status) {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $this->host->id,
        'status' => $status,
    ]);

    expect($this->policy->reject($reservation->user, $changeRequest))->toBeFalse();
})->with([
    ChangeRequestStatus::APPROVED,
    ChangeRequestStatus::REJECTED,
    ChangeRequestStatus::CANCELLED,
    ChangeRequestStatus::EXPIRED,
]);

it('denies host from rejecting a change request they created', function () {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $this->host->id,
        'status' => ChangeRequestStatus::PENDING,
    ]);

    expect($this->policy->reject($this->host, $changeRequest))->toBeFalse();
});

it('allows reservation owner to cancel a change request created by the host', function () {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $this->host->id,
        'status' => ChangeRequestStatus::PENDING,
    ]);

    expect($this->policy->cancel($reservation->user, $changeRequest))->toBeTrue();
});

it('allows host to cancel a change request created by the guest', function () {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $reservation->user_id,
        'status' => ChangeRequestStatus::PENDING,
    ]);

    expect($this->policy->cancel($this->host, $changeRequest))->toBeTrue();
});

it('denies cancelling a change request that is not pending', function (ChangeRequestStatus $status) {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $this->host->id,
        'status' => $status,
    ]);

    expect($this->policy->cancel($reservation->user, $changeRequest))->toBeFalse();
})->with([
    ChangeRequestStatus::APPROVED,
    ChangeRequestStatus::REJECTED,
    ChangeRequestStatus::CANCELLED,
    ChangeRequestStatus::EXPIRED,
]);

it('denies host from cancelling a change request they created', function () {
    $reservation = Reservation::factory()->create();

    $changeRequest = ChangeRequest::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $this->host->id,
        'status' => ChangeRequestStatus::PENDING,
    ]);

    expect($this->policy->cancel($this->host, $changeRequest))->toBeFalse();
});
