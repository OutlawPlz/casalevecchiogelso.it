<?php

use App\Actions\Charge;
use App\Models\User;

test('the guest can be charged and refund', function () {
    $user = User::factory()->guest()->create();

    $payment = (new Charge)($user, 1000, 'pm_card_visa');
});
