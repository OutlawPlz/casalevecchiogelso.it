<?php

namespace App\Http\Controllers;

use App\Actions\RefundGuest;
use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Services\Calendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Stripe\Refund;
use function App\Helpers\is_overnight_stay;
use function App\Helpers\refund_amount;

class ReservationController extends Controller
{
    /**
     * @param  Request  $request
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $reservations = Reservation::query();

        if ($authUser->isGuest()) {
            $reservations->where('user_id', $authUser->id);
        }

        $reservations = $reservations->simplePaginate();

        return view('reservation.index', [
            'authUser' => $authUser,
            'reservations' => $reservations,
        ]);
    }

    /**
     * @param  Request  $request
     * @param  Calendar  $calendar
     * @return RedirectResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function store(Request $request, Calendar $calendar): RedirectResponse
    {
        $attributes = $request->validate(self::rules());

        $reservation = new Reservation($attributes);

        $priceList = Product::defaultPriceList();

        array_walk($priceList, function (&$line) use ($reservation) {
            if (is_overnight_stay($line['product'])) $line['quantity'] = $reservation->nights;
        });

        $authUser = $request->user();

        $reservation->fill([
            'ulid' => Str::ulid(),
            'name' => $authUser->name,
            'email' => $authUser->email,
            'preparation_time' => new \DateInterval(config('reservation.preparation_time')),
            'user_id' => $authUser->id,
            'price_list' => $priceList
        ]);

        $calendar->sync();

        if ($calendar->isNotAvailable(...$reservation->reservedPeriod)) {
            throw ValidationException::withMessages([
                'unavailable_dates' => __('The selected dates are not available.')
            ]);
        }

        $reservation->save();

        session()->forget('reservation');

        return redirect()->route('reservation.show', [$reservation]);
    }

    /**
     * @param  Request  $request
     * @param  Reservation  $reservation
     * @return View
     */
    public function show(Request $request, Reservation $reservation): View
    {
        /** @var User $authUser */
        $authUser = $request->user();

        return view('reservation.show', [
            'authUser' => $authUser,
            'reservation' => $reservation,
        ]);
    }

    /**
     * @param Request $request
     * @param Reservation $reservation
     * @return View|RedirectResponse
     */
    public function delete(Request $request, Reservation $reservation): View|RedirectResponse
    {
        if (! $reservation->inStatus(ReservationStatus::CONFIRMED)) {
            return redirect()->route('reservation.show', [$reservation]);
        }

        /** @var User $authUser */
        $authUser = $request->user();

        return view('reservation.delete', [
            'authUser' => $authUser,
            'reservation' => $reservation,
            'refundAmount' => refund_amount($reservation),
        ]);
    }

    /**
     * @param Request $request
     * @param Reservation $reservation
     * @return RedirectResponse
     * @throws ValidationException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function destroy(Request $request, Reservation $reservation): RedirectResponse
    {
        $amount = refund_amount($reservation);

        if ($amount) (new RefundGuest)($reservation, $amount);

        $reservation->update(['status' => ReservationStatus::CANCELLED]);

        // TODO: Send notification to host and guest.

        return redirect()->route('reservation.show', [$reservation]);
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        $date = now()->addDays(2)->format('Y-m-d');

        return [
            'check_in' => ['required', 'date', "after:$date"],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guest_count' => ['required','numeric', 'min:1', 'max:10']
        ];
    }
}
