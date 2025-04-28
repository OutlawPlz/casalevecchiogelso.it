<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus as Status;
use App\Models\ChangeRequest;
use App\Models\Reservation;
use App\Models\User;
use App\Services\Calendar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use function App\Helpers\refund_factor;

class ChangeRequestController extends Controller
{
    public function create(Request $request, Reservation $reservation, Calendar $calendar): View
    {
        /** @var ?User $authUser */
        $authUser = $request->user();

        $refundFactor = refund_factor($reservation);

        if ($authUser?->isHost()) $refundFactor = 1;

        return view('change_request.create', [
            'authUser' => $authUser,
            'reservation' => $reservation,
            'unavailable' => $calendar->unavailableDates(ignore: $reservation),
            'refundFactor' => $refundFactor,
        ]);
    }

    /**
     * @throws ValidationException
     * @throws ApiErrorException
     */
    public function store(Request $request, Reservation $reservation, Calendar $calendar, StripeClient $stripe): void
    {
        $attributes = $request->validate(self::rules());

        $changeRequest = new ChangeRequest($attributes);

        $calendar->sync();

        if ($calendar->isNotAvailable($changeRequest->check_in, $changeRequest->check_out, ignore: $reservation)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        if ($reservation->inStatus(Status::PENDING)) {
            $stripe->checkout->sessions->expire($reservation->checkout_session['id']);
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
