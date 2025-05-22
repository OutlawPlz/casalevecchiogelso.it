<?php

namespace App\Actions;

use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class RefundGuest
{
    /**
     * @param Collection<Payment> $payments
     * @param int $cents
     * @return void
     * @throws ApiErrorException
     * @throws ValidationException
     */
    public function __invoke(Collection $payments, int $cents): void
    {
        if ($payments->sum('amountPaid') > $cents) {
            throw ValidationException::withMessages([
                'refund_amount' => 'The amount to refund is greater than the amount paid.',
            ]);
        }

        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        foreach ($payments as $payment) {
            if (! $payment->amountPaid) continue;

            $amount = min($cents, $payment->amountPaid);

            $stripe->refunds->create([
                'payment_intent' => $payment->payment_intent,
                'amount' => $amount,
                'metadata' => [
                    'reservation' => $payment->reservation_ulid,
                ],
            ]);

            $cents -= $amount;

            if (! $cents) break;
        }
    }
}
