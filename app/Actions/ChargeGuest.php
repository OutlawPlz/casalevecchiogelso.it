<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\App;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class ChargeGuest
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(User $user, int $amount, array $options = []): void
    {
        $paymentMethods = $user->paymentMethods();

        if (! $paymentMethods) throw new \RuntimeException('The user does not have any payment methods.');

        $parameters = array_merge([
            'amount' => $amount,
            'confirm' => true,
            'off_session' => true,
            'customer' => $user->stripe_id,
            'payment_method' => $paymentMethods[0]->id,
            'currency' => config('services.stripe.currency'),
        ], $options);

        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $stripe->paymentIntents->create($parameters);
    }
}
