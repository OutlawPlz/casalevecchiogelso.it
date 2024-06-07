<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

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
            throw new Exception("Price $stripeId not found, try to sync prices from Stripe.");
        }

        return $this->prices[$index];
    }

    /**
     * @param array $stripePrice
     * @return bool
     */
    public function put(array $stripePrice): bool
    {
        $index = array_search($stripePrice['id'], array_column($this->prices, 'id'));

        $index === false
            ? $this->prices[] = $stripePrice
            : $this->prices[$index] = $stripePrice;

        return Storage::put('prices.json', json_encode($this->prices, JSON_PRETTY_PRINT));
    }

    /**
     * @param string $stripeId
     * @return bool
     */
    public function delete(string $stripeId): bool
    {
        $index = array_search($stripeId, array_column($this->prices, 'id'));

        if ($index !== false) unset($this->prices[$index]);

        return Storage::put('prices.json', json_encode($this->prices, JSON_PRETTY_PRINT));
    }

    /**
     * @return bool
     * @throws ApiErrorException
     */
    public function sync(): bool
    {
        $this->prices = Cashier::stripe()->prices->all()['data'];

        return Storage::put('prices.json', json_encode($this->prices, JSON_PRETTY_PRINT));
    }
}
