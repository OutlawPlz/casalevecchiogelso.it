<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\App;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class Charge
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(User $user, int $amount, array $parameters = []): PaymentIntent
    {
        if (! array_key_exists('payment_method', $parameters)) {
            $parameters['payment_method'] = $user->defaultPaymentMethod()?->id;
        }

        $parameters = array_merge([
            'amount' => $amount,
            'confirm' => true,
            'off_session' => true,
            'customer' => $user->stripe_id,
            'currency' => config('services.stripe.currency'),
        ], $parameters);

        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        return $stripe->paymentIntents->create($parameters);
    }
}
