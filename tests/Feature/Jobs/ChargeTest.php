<?php

use App\Jobs\Charge;
use App\Models\Reservation;
use App\Models\User;
use Stripe\PaymentIntent;

test('a guest can be charged', function () {
    $guest = User::factory()->guest()->create();

    $reservation = Reservation::factory()->for($guest)->create();

    $paymentIntent = new Charge($guest, $reservation->tot, [
        'payment_method' => 'pm_card_visa',
        'metadata' => ['reservation' => $reservation->ulid],
    ])
        ->withFakeQueueInteractions()
        ->handle();

    expect($paymentIntent)
        ->toBeInstanceOf(PaymentIntent::class)
        ->and($paymentIntent->status)->toBe('succeeded')
        ->and($paymentIntent->amount)->toBe($reservation->tot)
        ->and($paymentIntent->customer)->toBe($guest->stripe_id)
        ->and($paymentIntent->metadata['reservation'])->toBe((string) $reservation->ulid);
});
