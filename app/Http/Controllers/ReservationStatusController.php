<?php

namespace App\Http\Controllers;

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
    /**
     * @param  Request  $request
     * @param  Reservation  $reservation
     * @return RedirectResponse
     * @throws \Exception
     */
    public function __invoke(Request $request, Reservation $reservation): RedirectResponse
    {
        $status = ReservationStatus::tryFrom(
            $request->validate(self::rules())['status']
        );

        return match ($status) {
            ReservationStatus::PENDING => $this->markAsPending($reservation),
            ReservationStatus::REJECTED => $this->markAsRejected($reservation),
            ReservationStatus::CANCELLED => $this->markAsCancelled($reservation),
        };
    }

    /**
     * @param  Reservation  $reservation
     * @return RedirectResponse
     */
    protected function markAsPending(Reservation $reservation): RedirectResponse
    {
        $reservation->update(['status' => ReservationStatus::PENDING]);

        /** @var User $authUser */
        $authUser = Auth::user();

        // TODO: Send a notification to the guest user.

        activity()
            ->performedOn($reservation)
            ->causedBy($authUser)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $authUser->email,
            ])
            ->log("The host :properties.user pre-approved the request.");

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
            ->log('The host :properties.user rejected the guest\'s request.');

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
