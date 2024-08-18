<?php

namespace App\View\Components;

use App\Models\Product;
use App\Models\Reservation;
use App\Services\Calendar;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ReservationQuote extends Component
{
    public array $unavailable_dates;

    public Product $overnightStay;

    public Product $cleaningFee;

    public Reservation $reservation;

    /**
     * @param Calendar $calendar
     * @throws \Exception
     */
    public function __construct(Calendar $calendar)
    {
        $this->unavailable_dates = $calendar->unavailableDates();

        $this->reservation = Reservation::fromSession();

        $this->overnightStay = Product::query()
            ->where('stripe_id', config('reservation.overnight_stay'))
            ->with('defaultPrice')
            ->firstOrFail();

        $this->cleaningFee = Product::query()
            ->where('stripe_id', config('reservation.cleaning_fee'))
            ->with('defaultPrice')
            ->firstOrFail();
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('components.reservation-quote');
    }
}
