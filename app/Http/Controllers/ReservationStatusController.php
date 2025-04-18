<?php

namespace App\Http\Controllers;

use App\Actions\ApproveReservation;
use App\Actions\RefundGuest;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Stripe\Exception\ApiErrorException;

class ReservationStatusController extends Controller
{
    public function __invoke(Request $request, Reservation $reservation): RedirectResponse
    {
        $status = ReservationStatus::tryFrom(
            $request->validate(self::rules())['status']
        );

        return match ($status) {
            ReservationStatus::PENDING => $this->markAsPending($reservation),
            ReservationStatus::REJECTED => $this->markAsRejected($reservation),
            ReservationStatus::CANCELLED => $this->markAsCancelled($reservation),
            default => throw new \RuntimeException("Unhandled reservation status: $status->value"),
        };
    }

    protected function markAsPending(Reservation $reservation): RedirectResponse
    {
        (new ApproveReservation)($reservation);

        // TODO: Notify the guest and the host.

        return redirect()->back();
    }

    /**
     * @param  Reservation  $reservation
     * @return RedirectResponse
     */
    protected function markAsRejected(Reservation $reservation): RedirectResponse
    {
        $reservation->update(['status' => ReservationStatus::REJECTED]);

        /** @var User $authUser */
        $authUser = Auth::user();

        activity()
            ->causedBy($authUser)
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $authUser->email,
            ])
            ->log('The host :properties.user rejected the request.');

        return redirect()->back();
    }

    /**
     * @param  Reservation  $reservation
     * @return RedirectResponse
     * @throws ApiErrorException
     */
    protected function markAsCancelled(Reservation $reservation): RedirectResponse
    {
        $reservation->update(['status' => ReservationStatus::CANCELLED]);

        // TODO: Notify the host and the guest.

        /** @var User $authUser */
        $authUser = Auth::user();

        activity()
            ->causedBy($authUser)
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $authUser->email,
            ])
            ->log("The $authUser->role :properties.user cancelled the reservation.");

        (new RefundGuest)($reservation);

        return redirect()->back();
    }

    /**
     * @return array[]
     */
    public static function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ReservationStatus::class)],
        ];
    }
}
