<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Price;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Payout;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;
use function App\Helpers\money_formatter;

class StripeController extends Controller
{
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

    protected function handleCheckoutSessionCompleted(Event $event): void
    {
        /** @var Session $session */
        $session = $event->data->object;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $session->metadata->reservation)
            ->firstOrFail();

        $checkoutSession = $reservation->checkout_session;

        $checkoutSession['setup_intent'] = $session->setup_intent;

        $reservation->update([
            'status' => ReservationStatus::CONFIRMED,
            'checkout_session' => $checkoutSession,
        ]);

        /** @var ?User $user */
        $user = User::query()
            ->where('stripe_id', $session->customer)
            ->first();

        activity()
            ->performedOn($reservation)
            ->causedBy($user)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'user' => $user?->email,
            ])
            ->log('The reservation has been confirmed.');
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

    protected function handlePaymentIntentPaymentFailed(Event $event): void
    {
        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $paymentIntent->metadata->reservation)
            ->firstOrFail();

        // TODO: Handle failure. Notify the user and setup a retry.

        $amount = money_formatter($paymentIntent->amount);

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'payment_intent' => $paymentIntent->id,
                'message' => $paymentIntent->last_payment_error->message,
                'doc_url' => $paymentIntent->last_payment_error->doc_url,
            ])
            ->log("A payment of $amount failed due to {$paymentIntent->last_payment_error->type}.");
    }

    /**
     * @throws ApiErrorException
     */
    protected function handlePaymentIntentSucceeded(Event $event): void
    {
        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $paymentIntent->metadata->reservation)
            ->firstOrFail();
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $charge = $stripe->charges->retrieve(
            $paymentIntent->latest_charge,
            ['expand' => ['balance_transaction']]
        );

        $paymentIntents = $reservation->payment_intents;

        $succeededPayment = [
            'id' => $paymentIntent->id,
            'customer' => $paymentIntent->customer,
            'status' => $paymentIntent->status,
            'amount' => $paymentIntent->amount,
            'amount_refunded' => $charge->amount_refunded,
            'stripe_fee' => $charge->balance_transaction->fee,
            'net_amount' => $charge->balance_transaction->net,
            'receipt_url' => $charge->receipt_url,
            'charge' => $charge->id,
        ];

        if (property_exists($paymentIntent->metadata, 'change_request')) {
            $succeededPayment['change_request'] = $paymentIntent->metadata->change_request;
        }

        $paymentIntents[] = $succeededPayment;

        $reservation->update(['payment_intents' => $paymentIntents]);

        $amount = money_formatter($paymentIntent->amount);

        activity()
            ->performedOn($reservation)
            ->withProperties([
                'reservation' => $reservation->ulid,
                'payment_intent' => $paymentIntent->id,
                'amount' => $paymentIntent->amount,
            ])
            ->log("The guest successfully paid $amount.");
    }

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

    protected function handleCustomerDeleted(Event $event): void
    {
        $user = User::query()
            ->where('stripe_id', $event->data->object->id)
            ->firstOrFail();

        $user->update(['stripe_id' => null]);
    }

    protected function handleProductUpdated(Event $event): void
    {
        /** @var \Stripe\Product $product */
        $product = $event->data->object;

        $attributes = Product::makeFromStripe($product)->toArray();

        Product::query()->updateOrCreate(['stripe_id' => $product->id], $attributes);
    }

    protected function handleProductCreated(Event $event): void
    {
        $this->handleProductUpdated($event);
    }

    protected function handleProductDeleted(Event $event): void
    {
        /** @var \Stripe\Product $product */
        $product = $event->data->object;

        Product::query()->where('stripe_id', $product->id)->delete();
    }

    protected function handlePriceUpdated(Event $event): void
    {
        /** @var \Stripe\Price $price */
        $price = $event->data->object;

        $attributes = Price::makeFromStripe($price)->toArray();

        Price::query()->updateOrCreate(['stripe_id' => $price->id], $attributes);
    }

    protected function handlePriceCreated(Event $event): void
    {
        $this->handlePriceUpdated($event);
    }

    protected function handlePriceDeleted(Event $event): void
    {
        /** @var \Stripe\Price $price */
        $price = $event->data->object;

        Price::query()->where('stripe_id', $price->id)->delete();
    }

    /**
     * @throws ApiErrorException
     */
    protected function handleRefundUpdated(Event $event): void
    {
        /** @var Refund $refund */
        $refund = $event->data->object;
        /** @var Reservation $reservation */
        $reservation = Reservation::query()
            ->where('ulid', $refund->metadata->reservation)
            ->firstOrFail();
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $charge = $stripe->charges->retrieve($refund->charge, ['expand' => ['balance_transaction']]);

        $paymentIntents = $reservation->payment_intents;

        array_walk($paymentIntents, function (&$paymentIntent) use ($charge) {
            if ($paymentIntent['id'] === $charge->payment_intent) {
                $paymentIntent = array_merge($paymentIntent, [
                    'amount' => $charge->amount,
                    'amount_refunded' => $charge->amount_refunded,
                    'stripe_fee' => $charge->balance_transaction->fee,
                    'net_amount' => $charge->balance_transaction->net,
                ]);
            }
        });

        $reservation->update(['payment_intents' => $paymentIntents]);

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
}
