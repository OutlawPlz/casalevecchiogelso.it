<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Stripe\StripeClient;

/**
 * @property int $id
 * @property string $currency
 * @property int $unit_amount
 * @property string $product
 */
class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'currency',
        'unit_amount',
        'product',
    ];

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
            'product' => $price->product,
        ], $prices);

        return static::query()->upsert(
            $attributes,
            ['stripe_id'],
            ['currency', 'unit_amount', 'product']
        );
    }
}
