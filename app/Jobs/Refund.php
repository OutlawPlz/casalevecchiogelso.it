<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Reservation;
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

    public int $backoff = 60;

    public function __construct(
        public Reservation $reservation,
        public int $amount,
        public array $metadata = [],
        public ?string $idempotencyKey = null
    ) {
        $this->idempotencyKey ??= (string) Str::ulid();
    }

    /**
     * @throws ApiErrorException
     */
    public function handle(): Collection
    {
        $payments = $this->reservation->payments;

        $cents = $this->amount;

        $amountPaid = $payments->reduce(fn ($tot, Payment $payment) => $tot + ($payment->amountPaid), 0);

        if ($amountPaid < $cents) {
            throw ValidationException::withMessages([
                'refund_amount' => 'The amount to refund is greater than the amount paid.',
            ]);
        }

        if (! $cents) {
            $cents = $amountPaid;
        }

        $stripe = app(StripeClient::class);

        $refunds = [];

        foreach ($payments as $payment) {
            if (! $payment->amountPaid) {
                continue;
            }

            $amount = min($cents, $payment->amountPaid);

            $refunds[] = $stripe->refunds->create([
                'payment_intent' => $payment->payment_intent,
                'amount' => $amount,
                'metadata' => array_merge([
                    'reservation' => $payment->reservation_ulid,
                ], $this->metadata),
            ], [
                'idempotency_key' => "{$payment->payment_intent}_{$this->idempotencyKey}",
            ]);

            $cents -= $amount;

            if (! $cents) {
                break;
            }
        }

        return collect($refunds);
    }
}
