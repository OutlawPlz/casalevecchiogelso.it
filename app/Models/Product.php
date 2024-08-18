<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;
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
    protected static function syncFromStripe(): int
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $products = $stripe->products->all()['data'];

        /** @var \Stripe\Product $product */
        $attributes = array_map(fn ($product) => [
            'stripe_id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'default_price' => $product->default_price
        ], $products);

        return static::query()->upsert(
            $attributes,
            ['stripe_id'],
            ['name', 'description', 'default_price']
        );
    }
}
