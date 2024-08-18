<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Support\Facades\App;
use Stripe\Product as StripeProduct;
use Stripe\StripeClient;

/**
 * @property int $id
 * @property string $stripe_id
 * @property string $name
 * @property ?string $description
 * @property string default_price
 * @property-read ?Price defaultPrice
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'name',
        'description',
        'default_price'
    ];

    /**
     * @return BelongsTo
     */
    public function defaultPrice(): BelongsTo
    {
        return $this->belongsTo(Price::class, 'default_price', 'stripe_id');
    }

    /**
     * @return bool
     */
    public function isOvernightStay(): bool
    {
        return $this->stripe_id === config('reservation.overnight_stay');
    }

    /**
     * @return int
     * @throws \Stripe\Exception\ApiErrorException
     */
    public static function syncFromStripe(): int
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $products = $stripe->products->all()['data'];

        /** @var StripeProduct $product */
        $attributes = array_map(
            fn ($product) => static::makeFromStripe($product)->toArray(),
            $products
        );

        return static::query()->upsert(
            $attributes,
            ['stripe_id'],
            ['name', 'description', 'default_price']
        );
    }

    /**
     * @param  StripeProduct  $product
     * @return static
     */
    public static function makeFromStripe(StripeProduct $product): static
    {
        return new static([
            'stripe_id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'default_price' => $product->default_price
        ]);
    }
}
