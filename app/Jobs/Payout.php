<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\Payout as StripePayout;
use Stripe\StripeClient;

use function App\Helpers\money_format;

class Payout implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [7, 21, 63];
    }

    public string $idempotencyKey;

    public function __construct(public Reservation $reservation)
    {
        $this->idempotencyKey = Str::ulid()->toString();
    }

    /**
     * @throws ApiErrorException
     */
    public function handle(): StripePayout
    {
        $netAmount = $this->reservation->payments->sum(fn ($payment) => $payment->netAmount);

        $stripe = app(StripeClient::class);

        $payout = $stripe->payouts->create([
            'amount' => $netAmount,
            'currency' => config('services.stripe.currency'),
            'metadata' => ['reservation' => $this->reservation->ulid,],
        ], [
            'idempotency_key' => $this->idempotencyKey
        ]);

        $this->reservation->update(['payout' => $payout->id]);

        activity()
            ->performedOn($this->reservation)
            ->withProperties([
                'reservation' => $this->reservation->ulid,
                'payout' => $payout->id,
                'amount' => $netAmount,
            ])
            ->log('A payout of '.money_format($netAmount).' has been requested.');

        return $payout;
    }
}
