<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ApiErrorException
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $ulid = $request->validate(self::rules())['reservation'];

        /** @var User $authUser */
        $authUser = $request->user();
        /** @var Reservation $reservation */
        $reservation = $authUser->reservations()->where('ulid', $ulid)->firstOrFail();

        $checkoutSession = Session::create([
            'line_items' => $reservation->order(),
            'customer' => $authUser->createAsStripeCustomer(),
            'mode' => 'payment',
            'success_url' => route('reservation.show', [$reservation]),
            'cancel_url' => route('reservation.show', [$reservation]),
            'metadata' => [
                'reservation' => $reservation->ulid,
            ],
        ]);

        activity()
            ->performedOn($reservation)
            ->causedBy($authUser)
            ->withProperties([
                'checkout_session' => $checkoutSession->id,
                'email' => $authUser->email,
                'reservation' => $reservation->ulid,
            ])
            ->log("The user :properties.email initiated a checkout session.");

        return redirect($checkoutSession->url);
    }

    /**
     * @return array[]
     */
    public static function rules(): array
    {
        return [
            'reservation' => ['required', 'exists:reservations,ulid'],
        ];
    }
}
