<?php

namespace App\Policies;

use App\Enums\ChangeRequestStatus as Status;
use App\Enums\ReservationStatus;
use App\Models\ChangeRequest;
use App\Models\User;

class ChangeRequestPolicy
{
    public function create(User $user, ChangeRequest $changeRequest): bool
    {
        $reservation = $changeRequest->reservation;

        $forbiddenStatuses = [
            ReservationStatus::CANCELLED,
            ReservationStatus::REJECTED,
            ReservationStatus::COMPLETED
        ];

        if ($reservation->inStatus(...$forbiddenStatuses)) return false;

        return $user->isHost() || $reservation->user()->is($user);
    }

    public function view(User $user, ChangeRequest $changeRequest): bool
    {
        return $user->isHost() || $changeRequest->reservation->user()->is($user);
    }

    public function approve(User $user, ChangeRequest $changeRequest): bool
    {
        if (! $changeRequest->inStatus(Status::PENDING)) return false;

        if ($changeRequest->user->isHost()) {
            return $changeRequest->reservation->user()->is($user);
        }

        return $user->isHost();
    }

    public function reject(User $user, ChangeRequest $changeRequest): bool
    {
        if (! $changeRequest->inStatus(Status::PENDING)) return false;

        if ($changeRequest->user->isHost()) {
            return $changeRequest->reservation->user()->is($user);
        }

        return $user->isHost();
    }

    public function cancel(User $user, ChangeRequest $changeRequest): bool
    {
        if (! $changeRequest->inStatus(Status::PENDING)) return false;

        if ($changeRequest->user->isHost()) {
            return $changeRequest->reservation->user()->is($user);
        }

        return $user->isHost();
    }
}
