<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class Charge implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public User $user,
        public int $amount,
        public array $metadata = [],
        public ?string $paymentMethod = null,
        public ?string $idempotencyKey = null
    ) {
        $this->idempotencyKey ??= (string) Str::ulid();
    }

    /**
     * @throws ApiErrorException
     */
    public function handle(): PaymentIntent
    {
        $parameters = array_filter([
            'amount' => $this->amount,
            'confirm' => true,
            'off_session' => true,
            'customer' => $this->user->stripe_id,
            'currency' => config('services.stripe.currency'),
            'payment_method' => $this->paymentMethod ?? $this->user->defaultPaymentMethod()?->id,
            'metadata' => $this->metadata,
        ]);

        $stripe = app(StripeClient::class);

        return $stripe->paymentIntents->create($parameters, [
            'idempotency_key' => $this->idempotencyKey,
        ]);
    }
}
