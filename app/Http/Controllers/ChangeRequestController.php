<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\ChangeRequest;
use App\Models\Reservation;
use App\Models\User;
use App\Services\Calendar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChangeRequestController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function store(Request $request, Reservation $reservation, Calendar $calendar): void
    {
        if ($reservation->inStatus(
            ReservationStatus::COMPLETED,
            ReservationStatus::CANCELLED,
            ReservationStatus::REJECTED
        )) {
            throw ValidationException::withMessages([
                'status' => __('Cannot make a change request on a reservation with :status status.'),
            ]);
        }

        $attributes = $request->validate(self::rules());

        $attributes['check_in'] .= ' ' . config('reservation.check_in_time');
        $attributes['check_out'] .= ' ' . config('reservation.check_out_time');

        $changeRequest = new ChangeRequest($attributes);

        $calendar->sync();

        if ($calendar->isNotAvailable($changeRequest->check_in, $changeRequest->check_out, ignore: $reservation)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        $reservation->changeRequests()->save($changeRequest);

        /** @var ?User $authUser */
        $authUser = $request->user();

        activity()
            ->performedOn($reservation)
            ->causedBy($authUser)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $authUser?->email,
            ])
            ->log("The $authUser?->role :properties.user has made a change request.");
    }

    public static function rules(?Reservation $reservation = null): array
    {
        $date = now()->addDays(2)->format('Y-m-d');

        $rules = [
            'check_in' => ['required', 'date', "after:$date"],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guest_count' => ['required','numeric', 'min:1', 'max:10']
        ];

        if ($reservation?->inProgress()) {
            $checkIn = $reservation->check_in->format('Y-m-d');

            $rules += [
                'check_in' => ['required', 'date', "date_equals:$checkIn"],
                'check_out' => ['required', 'date', Rule::date()->after($reservation->check_out)],
            ];
        }

        return $rules;
    }
}
