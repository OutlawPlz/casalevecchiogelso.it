<?php

use App\Jobs\Charge;
use App\Jobs\Refund;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Stripe\PaymentIntent;
use Stripe\Refund as StripeRefund;

test('user can be charged', function () {
    $user = User::factory()->guest()->create();

    $chargeJob = new Charge($user, 1000, paymentMethod: 'pm_card_visa');
    $paymentIntent = $chargeJob->handle();

    expect($chargeJob->idempotencyKey)->not->toBeNull()
        ->and($paymentIntent)
        ->toBeInstanceOf(PaymentIntent::class)
        ->and($paymentIntent->amount)->toBe(1000)
        ->and($paymentIntent->customer)->toBe($user->stripe_id);
});

test('user can be refunded', function () {
    $user = User::factory()->guest()->create();

    $chargeJob = new Charge($user, 1000, paymentMethod: 'pm_card_visa');
    $paymentIntent = $chargeJob->handle();

    $reservation = Reservation::factory()->create();

    Payment::create([
        'payment_intent' => $paymentIntent->id,
        'status' => $paymentIntent->status,
        'amount' => $paymentIntent->amount,
        'amount_captured' => $paymentIntent->amount,
        'amount_refunded' => 0,
        'fee' => 0,
        'customer' => $paymentIntent->customer,
        'reservation_ulid' => $reservation->ulid,
    ]);

    $refundJob = new Refund($reservation->fresh(), 1000);
    $refunds = $refundJob->handle();

    /** @var StripeRefund $refund */
    $refund = $refunds->first();

    expect($refundJob->idempotencyKey)->not->toBeNull()
        ->and($refund)
        ->toBeInstanceOf(StripeRefund::class)
        ->and($refund->amount)->toBe(1000)
        ->and($refund->payment_intent)->toBe($paymentIntent->id);

    expect($refunds->count())->toBe(1);
});
