<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\RateLimitException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class Charge implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [7, 21, 63];
    }

    public string $idempotencyKey;

    public function __construct(
        public User $user,
        public int $amount,
        public array $parameters = []
    ) {
        $this->idempotencyKey = Str::ulid()->toString();
    }

    /**
     * @throws ApiErrorException
     */
    public function handle(): PaymentIntent
    {
        if (! array_key_exists('payment_method', $this->parameters)) {
            $parameters['payment_method'] = $this->user->defaultPaymentMethod()?->id;
        }

        $parameters = array_replace_recursive([
            'amount' => $this->amount,
            'confirm' => true,
            'off_session' => true,
            'customer' => $this->user->stripe_id,
            'currency' => config('services.stripe.currency'),
        ], $this->parameters);

        $stripe = app(StripeClient::class);

        try {
            return $stripe->paymentIntents->create($parameters, ['idempotency_key' => $this->idempotencyKey]);
        } catch (ApiErrorException $exception) {
            if (! in_array($exception::class, [ApiConnectionException::class, RateLimitException::class])) {
                $this->fail($exception);
            }

            throw $exception;
        }
    }
}
