<?php

namespace App\Models;

use Database\Factories\RefundFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stripe\Refund as StripeRefund;

/**
 * @property int $id
 * @property string $stripe_id
 * @property string $payment_id
 * @property string $payment_intent
 * @property string $status
 * @property int $amount
 * @property string $reason
 * @property-read Payment $payment
 */
class Refund extends Model
{
    /** @use HasFactory<RefundFactory> */
    use HasFactory;

    public final function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $fillable = [
        'stripe_id',
        'payment_id',
        'payment_intent',
        'status',
        'amount',
        'reason',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public static function makeFrom(StripeRefund $refund): static
    {
        return new static([
            'stripe_id' => $refund->id,
            'payment_intent' => $refund->payment_intent,
            'amount' => $refund->amount,
            'reason' => $refund->reason,
            'status' => $refund->status,
        ]);
    }
}
