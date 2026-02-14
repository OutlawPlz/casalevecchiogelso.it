<?php

use App\Jobs\Charge;
use App\Jobs\Refund;
use App\Models\Reservation;
use App\Models\User;
use Database\Factories\ReservationFactory;

uses(Tests\TestCase::class);

beforeEach(function () {
    ReservationFactory::dontExpandRelationshipsByDefault();
});

it('generates an idempotency key for charge jobs', function () {
    $user = User::factory()->make();

    $job = new Charge($user, 1000);

    expect($job->idempotencyKey)->not->toBeNull()->toBeString();
});

it('generates an idempotency key for refund jobs', function () {
    $reservation = Reservation::factory()->make();

    $job = new Refund($reservation, 1000);

    expect($job->idempotencyKey)->not->toBeNull()->toBeString();
});

it('generates unique idempotency keys per job instance', function () {
    $user = User::factory()->make();

    $job1 = new Charge($user, 1000);
    $job2 = new Charge($user, 1000);

    expect($job1->idempotencyKey)->not->toBe($job2->idempotencyKey);
});

it('preserves a custom idempotency key', function () {
    $user = User::factory()->make();

    $job = new Charge($user, 1000, idempotencyKey: 'custom-key');

    expect($job->idempotencyKey)->toBe('custom-key');
});
