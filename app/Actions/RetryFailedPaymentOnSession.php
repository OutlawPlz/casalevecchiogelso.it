<?php

namespace App\Actions;

use App\Models\Payment;
use Illuminate\Support\Facades\App;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class RetryFailedPaymentOnSession
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(Payment $payment): Session
    {
        $checkoutSession = $this->createPaymentIntent($payment);

        $payment->reservation->update([
            'checkout_session' => [
                'id' => $checkoutSession->id,
                'url' => $checkoutSession->url,
                'expires_at' => $checkoutSession->expires_at,
            ]
        ]);

        // TODO: Notify the guest.

        return $checkoutSession;
    }

    /**
     * @throws ApiErrorException
     */
    protected function createPaymentIntent(Payment $payment): Session
    {
        $stripe = app(StripeClient::class);

        $lineItems = $payment->changeRequest
            ? $payment->changeRequest->toLineItems()
            : $payment->reservation->toLineItems();

        return $stripe->checkout->sessions->create([
            'customer' => $payment->customer,
            'currency' => config('services.stripe.currency'),
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('reservation.show', [$payment->reservation]),
            'cancel_url' => route('reservation.show', [$payment->reservation]),
            'metadata' => [
                'reservation' => $payment->reservation_ulid,
                'change_request' => $payment->change_request_ulid,
                'cancel_on_expire' => true,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'reservation' => $payment->reservation_ulid,
                    'change_request' => $payment->change_request_ulid,
                ]
            ],
        ]);
    }
}
