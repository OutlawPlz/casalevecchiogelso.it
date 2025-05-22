<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * @property int $id
 * @property string $payment_intent
 * @property string $status
 * @property int $amount
 * @property int $amount_captured
 * @property int $amount_refunded
 * @property int $fee
 * @property string $reservation_ulid
 * @property string $customer
 * @property string $charge
 * @property string $change_request_ulid
 * @property string $receipt_url
 * @property array $refunds
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
        'amount_captured',
        'amount_refunded',
        'fee',
        'reservation_ulid',
        'customer',
        'charge',
        'receipt_url',
        'change_request_ulid',
        'refunds',
    ];

    final public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'refunds' => 'array'
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_ulid', 'ulid');
    }

    public function changeRequest(): BelongsTo
    {
        return $this->belongsTo(ChangeRequest::class, 'change_request_ulid', 'ulid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer', 'stripe_id');
    }

    protected function netAmount(): Attribute
    {
        /** @uses static::$netAmount */
        return Attribute::make(
            get: fn () => $this->amount_captured - $this->fee - $this->amount_refunded
        );
    }

    protected function amountPaid(): Attribute
    {
        /** @uses static::$amountPaid */
        return Attribute::make(
            get: fn () => $this->amount_captured - $this->amount_refunded
        );
    }

    /**
     * @throws ApiErrorException
     */
    public function syncFromStripe(): bool
    {
        if (! $this->payment_intent) return false;

        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $paymentIntent = $stripe->paymentIntents->retrieve(
            'pi_3RMEFLAKSJP4UmE20jY687Vr',
            ['expand' => ['latest_charge.balance_transaction', 'latest_charge.refunds']]
        );

        $refunds = [];

        foreach ($paymentIntent->latest_charge->refunds->data ?? [] as $refund) {
            $refunds[] = [
                'id' => $refund->id,
                'amount' => $refund->amount,
                'status' => $refund->status,
            ];
        }

        return $this
            ->forceFill([
                'amount' => $paymentIntent->amount,
                'customer' => $paymentIntent->customer,
                'status' => $paymentIntent->status,
                'reservation_ulid' => @$paymentIntent->metadata->reservation,
                'change_request_ulid' => @$paymentIntent->metadata->change_request,
                'receipt_url' => $paymentIntent->latest_charge?->receipt_url,
                'amount_captured' => $paymentIntent->latest_charge->amount_captured ?? 0,
                'amount_refunded' => $paymentIntent->latest_charge->amount_refunded ?? 0,
                'fee' => $paymentIntent->latest_charge->balance_transaction->fee ?? 0,
                'refunds' => $refunds,
            ])
            ->save();
    }
}
