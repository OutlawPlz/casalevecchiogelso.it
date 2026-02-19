<?php

use App\Jobs\Charge;
use App\Models\Reservation;
use App\Models\User;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\CardException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

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

function fakePaymentIntents(Throwable $exception): void
{
    $service = Mockery::mock();

    $service->shouldReceive('create')->andThrow($exception);

    $stripe = new stdClass;

    $stripe->paymentIntents = $service;

    app()->instance(StripeClient::class, $stripe);
}

it('fails permanently', function () {
    $guest = User::factory()->guest()->create();

    fakePaymentIntents(new CardException('declined'));

    $charge = new Charge($guest, 100, ['payment_method' => 'pm_card_visa'])
        ->withFakeQueueInteractions();

    expect(fn () => $charge->handle())->toThrow(CardException::class);

    $charge->assertFailedWith(CardException::class);
});

it('retries failed jobs', function () {
    $guest = User::factory()->guest()->create();

    fakePaymentIntents(new ApiConnectionException('timeout'));

    $charge = new Charge($guest, 100, ['payment_method' => 'pm_card_visa'])
        ->withFakeQueueInteractions();

    expect(fn () => $charge->handle())->toThrow(ApiConnectionException::class);

    $charge->assertNotFailed();
});
