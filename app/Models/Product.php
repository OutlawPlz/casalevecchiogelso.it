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
 * @property bool $active
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'name',
        'description',
        'default_price',
        'active',
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
        return is_overnight_stay($this->stripe_id);
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
            ['name', 'description', 'default_price', 'active']
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
            'default_price' => $product->default_price,
            'active' => $product->active,
        ]);
    }

    /**
     * @return array<int, array{product: string, name: string, description: string, price: string, unit_amount: int, quantity: int}>
     */
    public static function defaultPriceList(): array
    {
        $products = static::query()
            ->where('active', true)
            ->with('defaultPrice')
            ->get();

        return $products
            ->map(fn (Product $product) => [
                'product' => $product->stripe_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->default_price,
                'unit_amount' => $product->defaultPrice->unit_amount,
                'quantity' => is_overnight_stay($product->stripe_id) ? 0 : 1,
            ])
            ->toArray();
    }
}
