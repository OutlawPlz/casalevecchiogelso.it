<?php

use App\Models\User;
use App\Policies\ActivityPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new ActivityPolicy;
});

it('allows host to view any activity', function () {
    $host = User::factory()->host()->create();

    expect($this->policy->viewAny($host))->toBeTrue();
});

it('denies guest from viewing any activity', function () {
    $guest = User::factory()->create();

    expect($this->policy->viewAny($guest))->toBeFalse();
});
