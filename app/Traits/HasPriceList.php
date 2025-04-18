<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property array{product: string, name: string, description: string, price: string, unit_amount: int, quantity: int}[] $price_list
 * @property-read int $tot
 */
trait HasPriceList
{
    public function toLineItems(): array
    {
        $order = [];

        foreach ($this->price_list as $line) {
            $order[] = [
                'price' => $line['price'],
                'quantity' => $line['quantity'],
            ];
        }

        return $order;
    }

    protected function tot(): Attribute
    {
        return Attribute::make(
            get: function () {
                return array_reduce(
                    $this->price_list,
                    fn ($tot, $line) => $tot + ($line['unit_amount'] * $line['quantity']),
                    0
                );
            }
        );
    }
}
