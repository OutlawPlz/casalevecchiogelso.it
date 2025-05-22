<?php

namespace App\Actions;

use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Stripe\StripeClient;

class Refund
{
    /**
     * @param Collection<Payment>|Payment $payments
     * @param int $cents
     * @return Collection<Refund>
     * @throws ApiErrorException
     * @throws ValidationException
     */
    public function __invoke(Collection|Payment $payments, int $cents = 0): Collection
    {
        if ($payments instanceof Payment) $payments = collect([$payments]);

        $amountPaid = $payments->reduce(fn ($tot, Payment $payment) => $tot + ($payment->amountPaid), 0);

        if ($amountPaid < $cents) {
            throw ValidationException::withMessages([
                'refund_amount' => 'The amount to refund is greater than the amount paid.',
            ]);
        }

        if (! $cents) $cents = $amountPaid;

        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $refunds = [];

        foreach ($payments as $payment) {
            if (! $payment->amountPaid) continue;

            $amount = min($cents, $payment->amountPaid);

            $refunds[] = $stripe->refunds->create([
                'payment_intent' => $payment->payment_intent,
                'amount' => $amount,
                'metadata' => [
                    'reservation' => $payment->reservation_ulid,
                ],
            ]);

            $cents -= $amount;

            if (! $cents) break;
        }

        return collect($refunds);
    }
}
