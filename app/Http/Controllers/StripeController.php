<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Price;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (UnexpectedValueException|SignatureVerificationException $exception) {
            report($exception);

            abort(400);
        }

        $method = 'handle' . Str::studly(str_replace('.', '_', $event->type));

        if (method_exists($this, $method)) {
            $this->$method($event);
        }

        return new Response('Webhook handled', 200);
    }

    /**
     * @param Event $event
     * @return void
     */
    protected function handleCheckoutSessionCompleted(Event $event): void
    {
        /** @var string $ulid */
        $ulid = $event->data->object->metadata->reservation;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $ulid)
            ->firstOrFail();

        $reservation->update(['status' => ReservationStatus::CONFIRMED]);

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $ulid,
                'checkout_session' => $event->data->object->id,
                'email' => $event->data->object->customer_email,
            ])
            ->log('The user :properties.email completed a checkout session. Reservation confirmed.');
    }

    /**
     * @param Event $event
     * @return void
     */
    protected function handleCustomerDeleted(Event $event): void
    {
        $user = User::query()->where('stripe_id', $event->data->object->id)->firstOrFail();

        $user->update(['stripe_id' => null]);
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handleProductUpdated(Event $event): void
    {
        /** @var \Stripe\Product $product */
        $product = $event->data->object;

        $attributes = Product::makeFromStripe($product)->toArray();

        Product::query()->updateOrCreate(['stripe_id' => $product->id], $attributes);
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handleProductCreated(Event $event): void
    {
        $this->handleProductUpdated($event);
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handleProductDeleted(Event $event): void
    {
        /** @var \Stripe\Product $product */
        $product = $event->data->object;

        Product::query()->where('stripe_id', $product->id)->delete();
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handlePriceUpdated(Event $event): void
    {
        /** @var \Stripe\Price $price */
        $price = $event->data->object;

        $attributes = Price::makeFromStripe($price)->toArray();

        Price::query()->updateOrCreate(['stripe_id' => $price->id], $attributes);
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handlePriceCreated(Event $event): void
    {
        $this->handlePriceUpdated($event);
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handlePriceDeleted(Event $event): void
    {
        /** @var \Stripe\Price $price */
        $price = $event->data->object;

        Price::query()->where('stripe_id', $price->id)->delete();
    }
}
