<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\StripeClient;

/**
 * @property int $id
 * @property string $payment_intent
 * @property string $status
 * @property int $amount
 * @property int $amount_refunded
 * @property int $fee
 * @property int $reservation_id
 * @property string $customer
 * @property string $charge
 * @property int $change_request_id
 * @property string $receipt_url
 * @property-read Reservation $reservation
 * @property-read ?ChangeRequest $changeRequest
 * @property-read User $user
 * @property-read int $netAmount
 * @property-read int $amountPaid
 */
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'payment_intent',
        'status',
        'amount',
        'amount_refunded',
        'fee',
        'reservation_id',
        'customer',
        'charge',
        'receipt_url',
        'change_request_id',
    ];

    final public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function changeRequest(): BelongsTo
    {
        return $this->belongsTo(ChangeRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer', 'stripe_id');
    }

    protected function netAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount - $this->fee - $this->amount_refunded
        );
    }

    protected function amountPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount - $this->amount_refunded
        );
    }

    /**
     * @throws ApiErrorException
     */
    public static function makeFrom(PaymentIntent $paymentIntent): static
    {
        return new static([
            'payment_intent' => $paymentIntent->id,
            'status' => $paymentIntent->status,
            'amount' => $paymentIntent->amount,
            'customer' => $paymentIntent->customer,

        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function refund(int $amount): Refund
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        return $stripe->refunds->create([
            'payment_intent' => $this->payment_intent,
            'amount' => $amount,
            'metadata' => [
                'reservation' => $this->reservation_id,
            ],
        ]);
    }
}
