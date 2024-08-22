<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Stripe\Price as StripePrice;
use Stripe\StripeClient;

/**
 * @property int $id
 * @property string $currency
 * @property int $unit_amount
 * @property string $product
 * @property bool $active
 */
class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'currency',
        'unit_amount',
        'product',
        'active',
    ];

    final public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @return int
     * @throws \Stripe\Exception\ApiErrorException
     */
    public static function syncFromStripe(): int
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $prices = $stripe->prices->all()['data'];

        $attributes = array_map(
            fn (StripePrice $price) => static::makeFromStripe($price)->toArray(),
            $prices
        );

        return static::query()->upsert(
            $attributes,
            ['stripe_id'],
            ['currency', 'unit_amount', 'product', 'active']
        );
    }

    /**
     * @param  StripePrice  $price
     * @return static
     */
    public static function makeFromStripe(StripePrice $price): static
    {
        return new static([
            'stripe_id' => $price->id,
            'currency' => $price->currency,
            'unit_amount' => $price->unit_amount,
            'product' => $price->product,
            'active' => $price->active,
        ]);
    }
}
