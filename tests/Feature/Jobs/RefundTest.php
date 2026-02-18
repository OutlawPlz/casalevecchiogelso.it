<?php

use App\Jobs\Charge;
use App\Jobs\Refund;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Stripe\Refund as StripeRefund;

test('a payment can be refunded', function () {
    $user = User::factory()->guest()->create();

    $paymentIntent = new Charge($user, 100, [
        'payment_method' => 'pm_card_visa',
        'metadata' => ['reservation' => Str::ulid()],
    ])
        ->withFakeQueueInteractions()
        ->handle();

    $payment = Payment::makeFromStripe($paymentIntent);

    $refunds = new Refund($payment, 100)
        ->withFakeQueueInteractions()
        ->handle();

    /** @var StripeRefund $refund */
    $refund = $refunds->first();

    expect($refund)
        ->toBeInstanceOf(StripeRefund::class)
        ->and($refund->amount)->toBe(100)
        ->and($refund->payment_intent)->toBe($paymentIntent->id);
});

it('splits refunds across multiple payments', function () {
    $user = User::factory()->guest()->create();

    $payments = collect();

    $count = 2;

    while ($count--) {
        $payments->add(
            new Charge($user, 100, [
                'payment_method' => 'pm_card_visa',
                'metadata' => ['reservation' => Str::ulid()],
            ])
                ->withFakeQueueInteractions()
                ->handle()
        );
    }

    /** @var Collection<Payment> $payments */
    $payments = $payments->map(fn ($payment) => Payment::makeFromStripe($payment));

    $refunds = new Refund($payments, 150)
        ->withFakeQueueInteractions()
        ->handle();

    expect($refunds)
        ->toHaveCount(2)
        ->and($refunds[0]->amount)->toBe(100)
        ->and($refunds[0]->payment_intent)->toBe($payments->first()->payment_intent)
        ->and($refunds[1]->amount)->toBe(50)
        ->and($refunds[1]->payment_intent)->toBe($payments->last()->payment_intent);
});
