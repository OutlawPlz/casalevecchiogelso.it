<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Cashier\Checkout;

class CheckoutController extends Controller
{
    /**
     * @param Request $request
     * @return Checkout
     */
    public function create(Request $request): Checkout
    {
        /** @var User $authUser */
        $authUser = $request->user();

        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where([
                ['ulid', $request->id],
                ['user_id', $authUser?->id]
            ])
            ->firstOrFail();

        return $authUser->checkout($reservation->order(), [
            'success_url' => route('reservation.show', [$reservation]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('reservation.show', [$reservation]),
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function success(Request $request): void
    {
        //
    }
}
