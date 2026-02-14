<?php

namespace App\View\Components;

use App\Models\Product;
use App\Services\Calendar;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ReservationQuote extends Component
{
    /** @var string[] */
    public array $unavailable_dates;

    /** @var array{product: string, name: string, description: string, price: string, unit_amount: int, quantity: int}[] */
    public array $priceList;

    /**
     * @throws \Exception
     */
    public function __construct(Calendar $calendar)
    {
        $this->unavailable_dates = $calendar->unavailableDates();

        $this->priceList = Product::defaultPriceList();
    }

    public function render(): View
    {
        return view('components.reservation-quote');
    }
}
