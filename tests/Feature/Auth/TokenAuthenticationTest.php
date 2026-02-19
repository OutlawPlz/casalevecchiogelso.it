<?php

use App\Models\User;
use App\Notifications\TokenLogin;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('sends a token login email', function () {
    Notification::fake();

    $this
        ->post('/auth/token', [
            'email' => 'john-doe@example.com',
            'name' => 'John Doe',
        ])
        ->assertSuccessful();

    Notification::assertSentOnDemand(
        TokenLogin::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'john-doe@example.com'
    );

    $this
        ->post('/auth/token', [
            'name' => '',
            'email' => 'invalid',
        ])
        ->assertInvalid(['email', 'name']);
});

it('creates a new user', function () {
    $user = User::factory()->make();

    $url = URL::temporarySignedRoute('auth.token', now()->addHour(), [
        'email' => $user->email,
        'name' => $user->name,
    ]);

    $this->get($url)->assertRedirect('/');

    $this->assertDatabaseHas('users', [
        'email' => $user->email,
        'name' => $user->name,
    ]);

    $this->assertAuthenticatedAs(
        User::query()->firstWhere('email', $user->email)
    );
});

it('logs-in an existing user', function () {
    $user = User::factory()->create();

    $url = URL::temporarySignedRoute('auth.token', now()->addHour(), [
        'email' => $user->email,
        'name' => $user->name,
    ]);

    $this->get($url)->assertRedirect('/');

    $this->assertAuthenticatedAs($user);

    $this->get('/auth/token?invalid-signature')->assertUnauthorized();
});
