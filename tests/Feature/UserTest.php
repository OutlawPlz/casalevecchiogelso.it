<?php

use App\Actions\ChargeGuest;
use App\Actions\RefundGuest;
use App\Models\User;
use function Pest\Laravel\assertDatabaseHas;

test('user can be charged', function () {
    $user = User::factory()->guest()->create();

    $payment = (new ChargeGuest)($user, 1000, 'pm_card_visa');

    assertDatabaseHas('payments', [
        'payment_intent' => $payment->payment_intent,
        'amount' => 1000,
        'status' => 'succeeded',
    ]);
});

test('the user can be charged and refunded', function () {
    $user = User::factory()->guest()->create();

    $payment = (new ChargeGuest)($user, 1000, 'pm_card_visa');

    assertDatabaseHas('payments', [
        'payment_intent' => $payment->payment_intent,
        'amount' => 1000,
        'status' => 'succeeded',
    ]);

    (new RefundGuest)($user->payments, 500);

    assertDatabaseHas('payments')
});
