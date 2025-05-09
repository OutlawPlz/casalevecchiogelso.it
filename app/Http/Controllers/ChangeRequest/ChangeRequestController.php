<?php

namespace App\Http\Controllers\ChangeRequest;

use App\Enums\ReservationStatus as Status;
use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Services\Calendar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use function App\Helpers\is_overnight_stay;
use function App\Helpers\refund_factor;

class ChangeRequestController extends Controller
{
    public function show(Request $request, Reservation $reservation, ChangeRequest $changeRequest): View
    {
        /** @var ?User $authUser */
        $authUser = $request->user();

        $refundFactor = refund_factor($reservation, $changeRequest->created_at);

        if ($authUser?->isHost()) $refundFactor = 1;

        $refundAmount = 0;

        if ($changeRequest->reservation->hasBeenPaid()
            && $changeRequest->priceDifference() < 0) {
            $refundAmount = $changeRequest->priceDifference() * -$refundFactor;
        }

        $amountDue = 0;

        if ($changeRequest->reservation->hasBeenPaid()
            && $changeRequest->priceDifference() > 0) {
            $amountDue = $changeRequest->priceDifference();
        }

        return view('change_request.show', [
            'reservation' => $reservation,
            'authUser' => $authUser,
            'changeRequest' => $changeRequest,
            'refundAmount' => $refundAmount,
            'amountDue' => $amountDue,
        ]);
    }

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
    public function store(Request $request, Reservation $reservation, Calendar $calendar, StripeClient $stripe): array
    {
        $attributes = $request->validate(self::rules());
        /** @var ?User $authUser */
        $authUser = $request->user();

        $reason = $attributes['reason']; unset($attributes['reason']);

        $changeRequest = ChangeRequest::for($reservation)->fill([
            'user_id' => $authUser?->id,
            'reason' => $reason,
            'to' => $attributes,
        ]);

        $priceList = Product::defaultPriceList();

        array_walk($priceList, function (&$line) use ($changeRequest) {
            if (is_overnight_stay($line['product'])) $line['quantity'] = $changeRequest->toReservation->nights;
        });

        $changeRequest->fill(['to' => $attributes + ['price_list' => $priceList]]);

        $calendar->sync();

        [$checkIn, $checkOut] = $changeRequest->toReservation->reservedPeriod;

        if ($calendar->isNotAvailable($checkIn, $checkOut, ignore: $reservation)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        if ($reservation->inStatus(Status::PENDING)) {
            $stripe->checkout->sessions->expire($reservation->checkout_session['id']);
        }

        $reservation->changeRequests()->save($changeRequest);

        activity()
            ->performedOn($reservation)
            ->causedBy($authUser)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $authUser?->email,
            ])
            ->log("The $authUser?->role has made a change request.");

        return ['redirect' => route('reservation.show', [$reservation])];
    }

    public static function rules(?Reservation $reservation = null): array
    {
        $date = now()->addDays(2)->format('Y-m-d');

        $rules = [
            'check_in' => ['required', 'date', "after:$date"],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guest_count' => ['required','numeric', 'min:1', 'max:10'],
            'reason' => ['required', 'string', 'max:255'],
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
