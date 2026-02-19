<?php

use App\Models\User;

it('redirects to the billing portal', function () {
    $user = User::factory()->guest()->create();

    $this
        ->post(route('billing_portal'))
        ->assertRedirect(route('login'));

    $this
        ->actingAs($user)
        ->post(route('billing_portal'))
        ->assertRedirectContains('billing.stripe.com');
});
