<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;
use Stripe\Product as StripeProduct;
use Stripe\StripeClient;

use function App\Helpers\is_overnight_stay;

/**
 * @property int $id
 * @property string $stripe_id
 * @property string $name
 * @property ?string $description
 * @property string $default_price
 * @property-read ?Price $defaultPrice
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

    final public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function defaultPrice(): BelongsTo
    {
        return $this->belongsTo(Price::class, 'default_price', 'stripe_id');
    }

    /**
     * @throws \Stripe\Exception\ApiErrorException
     */
    public static function syncFromStripe(): int
    {
        /** @var StripeClient $stripe */
        $stripe = App::make(StripeClient::class);

        $products = $stripe->products->all()['data'];

        $attributes = array_map(
            fn (StripeProduct $product) => static::makeFromStripe($product)->toArray(),
            $products
        );

        return static::query()->upsert(
            $attributes,
            ['stripe_id'],
            ['name', 'description', 'default_price', 'active']
        );
    }

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
     * The first element is the overnightStay product.
     *
     * @return array{product: string, name: string, description: string, price: string, unit_amount: int, quantity: int}[]
     */
    public static function defaultPriceList(): array
    {
        $products = static::query()
            ->where('active', true)
            ->with('defaultPrice')
            ->get();

        $priceList = $products
            ->map(fn (Product $product) => [
                'product' => $product->stripe_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->default_price,
                'unit_amount' => $product->defaultPrice->unit_amount,
                'quantity' => is_overnight_stay($product->stripe_id) ? 0 : 1,
            ])
            ->toArray();

        // Makes overnightStay the first element.
        usort($priceList, fn ($line) => $line['quantity']);

        return $priceList;
    }
}
