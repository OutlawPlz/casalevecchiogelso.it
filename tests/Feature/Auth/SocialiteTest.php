<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

it('creates a new user', function () {
    $user = User::factory()->make();

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => $user->name,
        'email' => $user->email,
    ]));

    foreach (['/auth/fake/redirect', '/auth/fake/callback'] as $route) {
        $this->get($route)->assertNotFound();
    }

    foreach (['/auth/google/redirect', '/auth/google/callback'] as $route) {
        $this->get($route)->assertRedirect();
    }

    $this->assertDatabaseHas('users', [
        'email' => $user->email,
        'name' => $user->name,
    ]);

    $this->assertAuthenticatedAs(User::query()->firstWhere('email', $user->email));
});

it('logs-in existing users', function () {
    $user = User::factory()->create();

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-123',
        'email' => $user->email,
    ]));

    $this->get('/auth/google/callback')->assertRedirect();

    $this->assertAuthenticatedAs($user);
});
