<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\ChangeRequest;
use App\Models\Price;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Payout;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;
use function App\Helpers\money_formatter;

class StripeController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        Stripe::setApiKey(config('services.stripe.secret'));

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
        /** @var Session $session */
        $session = $event->data->object;

        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $session->metadata->reservation)
            ->firstOrFail();

        if (property_exists($session->metadata, 'change_request')) {
            $changeRequest = ChangeRequest::query()
                ->findOrFail($session->metadata->change_request);

            $reservation->apply($changeRequest)->push();
        }

        $reservation->update([
            'status' => ReservationStatus::CONFIRMED,
            'checkout_session' => null,
        ]);

        // TODO: Notify reservation confirmed.

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $session->metadata->reservation,
                'checkout_session' => $session->id,
                'payment_intent' => $session->payment_intent,
                'user' => $session->customer_details->email,
            ])
            ->log('The user :properties.user completed a checkout session.');
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handlePaymentIntentPaymentFailed(Event $event): void
    {
        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $paymentIntent->metadata->reservation)
            ->firstOrFail();

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'payment_intent' => $paymentIntent->id,
                'message' => $paymentIntent->last_payment_error->message,
                'doc_url' => $paymentIntent->last_payment_error->doc_url,
            ])
            ->log("Payment failed due to {$paymentIntent->last_payment_error->type}.");
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handlePaymentIntentCreated(Event $event): void
    {
        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $paymentIntent->metadata->reservation)
            ->firstOrFail();

        $reservation->update(['payment_intent' => $paymentIntent->id]);
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handlePayoutFailed(Event $event): void
    {
        /** @var Payout $payout */
        $payout = $event->data->object;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $payout->metadata->reservation)
            ->firstOrFail();

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'payout' => $payout->id,
                'failure_code' => $payout->failure_code,
                'failure_message' => $payout->failure_message,
            ])
            ->log("The payout has failed due to $payout->failure_code.");
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handlePayoutPaid(Event $event): void
    {
        /** @var Payout $payout */
        $payout = $event->data->object;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $payout->metadata->reservation)
            ->firstOrFail();

        $amount = money_formatter($payout->amount);

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'payout' => $payout->id,
            ])
            ->log("The payout of $amount has been credited.");
    }

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handlePayoutCanceled(Event $event): void
    {
        /** @var Payout $payout */
        $payout = $event->data->object;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $payout->metadata->reservation)
            ->firstOrFail();

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'payout' => $payout->id,
            ])
            ->log('The payout has been cancelled.');
    }

    /**
     * @param Event $event
     * @return void
     */
    protected function handleCustomerDeleted(Event $event): void
    {
        $user = User::query()
            ->where('stripe_id', $event->data->object->id)
            ->firstOrFail();

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

    /**
     * @param  Event  $event
     * @return void
     */
    protected function handleChargeRefundUpdated(Event $event): void
    {
        /** @var Refund $refund */
        $refund = $event->data->object;

        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $refund->metadata->reservation)
            ->firstOrFail();

        $message = match ($refund->status) {
            'pending' => 'The refund process is pending.',
            'requires_action' => "The refund process requires action ({$refund->next_action->type}).",
            'succeeded' => 'The refund process is completed.',
            'failed' => "The refund failed due to $refund->failure_reason.",
            'cancelled' => "The refund was canceled due to $refund->failure_reason.",
            default => throw new \RuntimeException("Unhandled refund status: $refund->status"),
        };

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'refund' => $refund->id,
                'amount' => $refund->amount,
            ])
            ->log($message);
    }

    protected function handleCheckoutSessionExpired(Event $event): void
    {
        /** @var string $ulid */
        $ulid = $event->data->object->metadata->reservation;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()->where('ulid', $ulid)->firstOrFail();

        $reservation->update([
            'checkout_session' => null,
            'status' => ReservationStatus::QUOTE,
        ]);

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
            ])
            ->log("The pre-approval has expired.");

        // TODO: Notify the guest and the host that pre-approval has expired..
    }
}
