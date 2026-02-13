<?php

namespace App\Jobs;

use App\Actions\Refund;
use App\Models\ChangeRequest;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

class ProcessChangeRequestRefund implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public ChangeRequest $changeRequest,
        public int $amount
    ) {}

    /**
     * @throws ApiErrorException
     * @throws ValidationException
     */
    public function handle(): void
    {
        /** @var Collection<Payment> $payments */
        $payments = $this->changeRequest->reservation->payments;

        (new Refund)($payments, $this->amount, [
            'change_request' => $this->changeRequest->ulid,
        ]);
    }
}
