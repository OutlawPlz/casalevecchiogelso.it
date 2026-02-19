<?php

use App\Models\User;

it('saves the locale', function () {
    $this
        ->post(route('locale-preference'), ['locale' => 'en'])
        ->assertRedirect();

    expect(session('locale'))->toBe('en');

    $user = User::factory()->create(['locale' => 'it']);

    $this
        ->actingAs($user)
        ->post(route('locale-preference'), ['locale' => 'en'])
        ->assertRedirect();

    expect($user->fresh()->locale)->toBe('en');
});

it('rejects invalid locales', function () {
    $this
        ->post(route('locale-preference'), ['locale' => 'xx'])
        ->assertInvalid(['locale']);

    $this
        ->post(route('locale-preference'), [])
        ->assertInvalid(['locale']);
});
