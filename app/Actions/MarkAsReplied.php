<?php

namespace App\Actions;

use App\Models\Message;
use App\Models\Reservation;

class MarkAsReplied
{
    /**
     * @param  Reservation  $reservation
     * @param  Message  $message
     * @return void
     */
    public function __invoke(Reservation $reservation, Message $message): void
    {
        $reservation->update(['replied_at' => $message->created_at]);
    }
}
