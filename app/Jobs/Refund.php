<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class Refund implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [7, 21, 63];
    }

    public string $idempotencyKey;

    public function __construct(
        public Collection|Payment $payments,
        public int $cents = 0,
        public array $parameters = []
    ) {
        $this->idempotencyKey = Str::ulid()->toString();
    }

    /**
     * @return Collection<Refund>
     * @throws ApiErrorException
     */
    public function handle(): Collection
    {
        if ($this->payments instanceof Payment) {
            $this->payments = collect([$this->payments]);
        }

        $amountPaid = $this->payments->reduce(fn ($tot, Payment $payment) => $tot + ($payment->amountPaid), 0);

        if ($amountPaid < $this->cents) {
            throw ValidationException::withMessages([
                'refund_amount' => 'The amount to refund is greater than the amount paid.',
            ]);
        }

        if (! $this->cents) {
            $this->cents = $amountPaid;
        }

        $stripe = app(StripeClient::class);

        $refunds = [];

        foreach ($this->payments as $payment) {
            if (! $payment->amountPaid) {
                continue;
            }

            $amount = min($this->cents, $payment->amountPaid);

            $parameters = array_replace_recursive([
                'payment_intent' => $payment->payment_intent,
                'amount' => $amount,
                'metadata' => ['reservation' => $payment->reservation_ulid],
            ], $this->parameters);

            $refunds[] = $stripe->refunds->create($parameters, [
                'idempotency_key' => "{$payment->payment_intent}_{$this->idempotencyKey}",
            ]);

            $this->cents -= $amount;

            if (! $this->cents) {
                break;
            }
        }

        return collect($refunds);
    }
}
