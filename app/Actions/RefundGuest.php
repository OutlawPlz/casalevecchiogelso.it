<?php

namespace App\Actions;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Throwable;
use function App\Helpers\money_formatter;

class RefundGuest
{
    /**
     * @param Collection<Payment> $payments
     * @param int $cents
     * @return void
     * @throws ApiErrorException
     * @throws ValidationException|Throwable
     */
    public function __invoke(Collection $payments, int $cents): void
    {
        if ($payments->sum('amountPaid') > $cents) {
            throw ValidationException::withMessages([
                'refund_amount' => 'The amount to refund is greater than the amount paid.',
            ]);
        }

        /** @var ?User $authUser */
        $authUser = Auth::user();

        foreach ($payments as $payment) {
            if (! $payment->amountPaid) continue;

            $amount = min($cents, $payment->amountPaid);

            $refund = $payment->refund($amount);

            $formattedAmount = money_formatter($refund->amount);

            activity()
                ->causedBy($authUser)
                ->performedOn($payment->reservation)
                ->withProperties([
                    'user' => $authUser?->email,
                    'reservation' => $payment->reservation_ulid,
                    'refund' => $refund->id,
                    'amount' => $refund->amount,
                ])
                ->log("A refund of $formattedAmount has been created.");

            $cents -= $amount;

            if (! $cents) break;
        }
    }
}
