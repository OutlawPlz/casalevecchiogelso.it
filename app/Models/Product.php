<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;
use Stripe\StripeClient;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'name',
        'description',
        'default_price_id'
    ];

    /**
     * @return BelongsTo
     */
    public function defaultPrice(): BelongsTo
    {
        return $this->belongsTo(Price::class, 'default_price_id', 'stripe_id');
    }

    /**
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'product_id');
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
            'default_price_id' => $product->default_price
        ], $products);

        return static::query()->upsert(
            $attributes,
            ['stripe_id'],
            ['name', 'description', 'default_price_id']
        );
    }
}
