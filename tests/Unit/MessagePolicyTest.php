<?php

use App\Models\Reservation;
use App\Models\User;
use App\Policies\MessagePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new MessagePolicy;

    $this->host = User::factory()->host()->create();
});

it('allows host to participate in messages', function () {
    $reservation = Reservation::factory()->create();

    expect($this->policy->participate($this->host, $reservation))->toBeTrue();
});

it('allows reservation owner to participate in messages', function () {
    $reservation = Reservation::factory()->create();

    expect($this->policy->participate($reservation->user, $reservation))->toBeTrue();
});

it('denies a stranger from participating in messages', function () {
    $stranger = User::factory()->create();
    $reservation = Reservation::factory()->create();

    expect($this->policy->participate($stranger, $reservation))->toBeFalse();
});
