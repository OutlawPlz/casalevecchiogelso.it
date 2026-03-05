<?php

use App\Models\Reservation;
use App\Models\User;
use App\View\Components\Chat;

beforeEach(function () {
    $this->reservation = Reservation::factory()->create();

    view()->share('errors', new \Illuminate\Support\MessageBag());
});

test('renders with correct urls', function () {
    $this->component(Chat::class, ['channel' => $this->reservation->ulid])
        ->assertSee(route('message.index', $this->reservation).'?page=1', false)
        ->assertSee(route('message.store', $this->reservation), false);

});

test('shows reservation feed button for hosts', function () {
    $this->actingAs(User::factory()->host()->create());

    $this->component(Chat::class, ['channel' => $this->reservation->ulid])
        ->assertSee('Reservation feed');
});

test('does not show reservation feed button for non-hosts', function () {
    $this->actingAs(User::factory()->create());

    $this->component(Chat::class, ['channel' => $this->reservation->ulid])
        ->assertDontSee('Reservation feed');
});
