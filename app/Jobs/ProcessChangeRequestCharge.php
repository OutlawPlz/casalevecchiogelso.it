<?php

namespace App\Jobs;

use App\Actions\Charge;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Stripe\Exception\ApiErrorException;

class ProcessChangeRequestCharge implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public User $user,
        public int $amount,
        public ChangeRequest $changeRequest
    ) {}

    /**
     * @throws ApiErrorException
     */
    public function handle(): void
    {
        (new Charge)($this->user, $this->amount, [
            'metadata' => [
                'reservation' => $this->changeRequest->reservation->ulid,
                'change_request' => $this->changeRequest->ulid,
                'retry_on_failure' => true,
            ],
        ]);
    }
}
