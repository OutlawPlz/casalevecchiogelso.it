<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    /**
     * @param  Request  $request
     * @param  StripeClient  $stripe
     * @return RedirectResponse
     * @throws ApiErrorException
     */
    public function __invoke(Request $request, StripeClient $stripe): RedirectResponse
    {
        $ulid = $request->validate(self::rules())['reservation'];

        /** @var User $authUser */
        $authUser = $request->user();
        /** @var Reservation $reservation */
        $reservation = $authUser->reservations()->where('ulid', $ulid)->firstOrFail();

        $checkoutSession = $stripe->checkout->sessions->create([
            'line_items' => $reservation->order(),
            'customer' => $authUser->createAsStripeCustomer(),
            'mode' => 'payment',
            'success_url' => route('reservation.show', [$reservation]),
            'cancel_url' => route('reservation.show', [$reservation]),
            'metadata' => [
                'reservation' => $reservation->ulid,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'reservation' => $reservation->ulid,
                ]
            ],
        ]);

        activity()
            ->performedOn($reservation)
            ->causedBy($authUser)
            ->withProperties([
                'checkout_session' => $checkoutSession->id,
                'user' => $authUser->email,
                'reservation' => $reservation->ulid,
            ])
            ->log("The $authUser->role :properties.user initiated a checkout session.");

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
