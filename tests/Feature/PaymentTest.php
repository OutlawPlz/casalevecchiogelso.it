<?php

use App\Actions\Charge;
use App\Actions\Refund;
use App\Models\Payment;
use App\Models\User;
use Stripe\PaymentIntent;
use Stripe\Refund as StripeRefund;

test('user can be charged', function () {
    $user = User::factory()->guest()->create();

    $paymentIntent = (new Charge)($user, 1000, ['payment_method' => 'pm_card_visa']);

    expect($paymentIntent)
        ->toBeInstanceOf(PaymentIntent::class)
        ->and($paymentIntent->amount)->toBe(1000)
        ->and($paymentIntent->customer)->toBe($user->stripe_id);
});

test('user can be refunded', function () {
    $user = User::factory()->guest()->create();

    $paymentIntent = (new Charge)($user, 1000, ['payment_method' => 'pm_card_visa']);

    $payment = Payment::makeFromStripe($paymentIntent);

    $refunds = (new Refund)($payment, 1000);

    /** @var StripeRefund $refund */
    $refund = $refunds->first();

    expect($refund)
        ->toBeInstanceOf(StripeRefund::class)
        ->and($refund->amount)->toBe(1000)
        ->and($refund->payment_intent)->toBe($paymentIntent->id);

    $count = $refunds->count();

    expect($count)->toBe(1);
});
