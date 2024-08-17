<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;
use Stripe\StripeClient;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'currency',
        'unit_amount',
        'product_id',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'stripe_id');
    }

    /**
     * @return int
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected static function syncFromStripe(): int
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $prices = $stripe->prices->all()['data'];

        /** @var \Stripe\Price $price */
        $attributes = array_map(fn ($price) => [
            'stripe_id' => $price->id,
            'currency' => $price->currency,
            'unit_amount' => $price->unit_amount,
            'product_id' => $price->product,
        ], $prices);

        return static::query()->upsert(
            $attributes,
            ['stripe_id'],
            ['currency', 'unit_amount', 'product_id']
        );
    }
}
