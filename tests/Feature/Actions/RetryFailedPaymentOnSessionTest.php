<?php

use App\Actions\RetryFailedPaymentOnSession;
use App\Models\ChangeRequest;
use App\Models\Payment;
use App\Models\Reservation;
use Stripe\Checkout\Session;

it('retries a failed payment on session', function () {
    $payment = Payment::factory()->failed()->create([
        'reservation_ulid' => Reservation::factory()->create()->ulid,
    ]);

    $session = (new RetryFailedPaymentOnSession)($payment);

    expect($session)->toBeInstanceOf(Session::class);

    expect($payment->reservation->fresh()->checkout_session)
        ->toBeArray()
        ->and($payment->reservation->fresh()->checkout_session['id'])->toBe($session->id)
        ->and($payment->reservation->fresh()->checkout_session['url'])->toBeUrl();
});

it('retries a failed payment on session with a change request', function () {
    $changeRequest = ChangeRequest::factory()->amountIncrease()->create();

    $payment = Payment::factory()->failed()->create([
        'reservation_ulid' => $changeRequest->reservation->ulid,
        'change_request_ulid' => $changeRequest->ulid,
    ]);

    $session = (new RetryFailedPaymentOnSession)($payment);

    expect($session)->toBeInstanceOf(Session::class);

    expect($payment->reservation->fresh()->checkout_session)
        ->toBeArray()
        ->and($payment->reservation->fresh()->checkout_session['id'])->toBe($session->id)
        ->and($payment->reservation->fresh()->checkout_session['url'])->toBeUrl();
});
