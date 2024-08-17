<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class Price
{
    /** @var array[]  */
    protected array $prices;

    public function __construct()
    {
        $this->prices = Storage::json('prices.json') ?? [];
    }

    /**
     * @param string $stripeId
     * @return array
     * @throws Exception
     */
    public function get(string $stripeId): array
    {
        $index = array_search($stripeId, array_column($this->prices, 'id'));

        if ($index === false) {
            throw new Exception("Price \"$stripeId\" not found, you should sync prices from Stripe");
        }

        return $this->prices[$index];
    }

    /**
     * @return bool
     * @throws ApiErrorException
     */
    public function sync(): bool
    {
        $client = new StripeClient(config('services.stripe.secret'));

        $this->prices = $client->prices->all(['expand' => ['data.product']])['data'];

        return Storage::put('prices.json', json_encode($this->prices, JSON_PRETTY_PRINT));
    }
}
