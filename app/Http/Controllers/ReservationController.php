<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Product;
use App\Models\Reservation;
use App\Services\Calendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    /**
     * @param  Request  $request
     * @param  Calendar  $calendar
     * @return RedirectResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function store(Request $request, Calendar $calendar): RedirectResponse
    {
        $authUser = $request->user();

        $reservation = Reservation::fromSession();

        /** @var Product $overnightStay */
        $overnightStay = Product::query()
            ->where('stripe_id', config('reservation.overnight_stay'))
            ->firstOrFail();

        /** @var Product $cleaningFee */
        $cleaningFee = Product::query()
            ->where('stripe_id', config('reservation.cleaning_fee'))
            ->firstOrFail();

        $reservation->fill([
            'ulid' => Str::ulid(),
            'name' => $authUser->name,
            'email' => $authUser->email,
            'preparation_time' => new \DateInterval(config('reservation.preparation_time')),
            'user_id' => $authUser->id,
            'price_list' => [
                $overnightStay->stripe_id => $overnightStay->default_price,
                $cleaningFee->stripe_id => $cleaningFee->default_price,
            ]
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
     * @param Reservation $reservation
     * @return View
     */
    public function show(Reservation $reservation): View
    {
        $products = Product::query()
            ->whereIn(
                'stripe_id',
                array_keys($reservation->price_list)
            )
            ->get();

        $prices = Price::query()
            ->whereIn(
                'stripe_id',
                array_values($reservation->price_list)
            )
            ->get();

        $priceList = [];

        foreach ($reservation->price_list as $productId => $priceId) {
            $priceList[] = [
                'product' => $products->first(fn ($product) => $product->stripe_id === $productId),
                'price' => $prices->first(fn ($price) => $price->stripe_id === $priceId),
                'quantity' => is_overnight_stay($productId) ? $reservation->nights : 1,
            ];
        }

        return view('reservation.show', [
            'reservation' => $reservation,
            'priceList' => $priceList,
        ]);
    }
}
