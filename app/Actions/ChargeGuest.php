<?php

namespace App\Actions;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class ChargeGuest
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(User $user, int $amount, ?string $paymentMethod = null, array $options = []): Payment
    {
        $paymentMethod ??= $user->defaultPaymentMethod()?->id;

        $parameters = array_merge([
            'amount' => $amount,
            'confirm' => true,
            'off_session' => true,
            'customer' => $user->stripe_id,
            'payment_method' => $paymentMethod,
            'currency' => config('services.stripe.currency'),
        ], $options);

        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $paymentIntent = $stripe->paymentIntents->create($parameters);

        $payment = Payment::makeFrom($paymentIntent);

        $user->payments()->save($payment);

        return $payment;
    }
}
