<?php

namespace App\Http\Controllers;

use App\Actions\RefundGuest;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;

class RefundController extends Controller
{
    /**
     * @param Request $request
     * @param Reservation $reservation
     * @return void
     * @throws ApiErrorException
     */
    public function store(Request $request, Reservation $reservation): void
    {
        $amount = $request->validate(self::rules($reservation))['amount'];

        (new RefundGuest)($reservation, $amount * 100);
    }

    /**
     * @param Reservation $reservation
     * @return array[]
     */
    public static function rules(Reservation $reservation): array
    {
        $tot = $reservation->tot / 100;

        return [
            'amount' => ['required', 'numeric', 'min:1', "max:$tot"],
        ];
    }
}
