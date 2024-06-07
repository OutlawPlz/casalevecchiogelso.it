<?php

namespace App\Listeners;

use App\Services\Price;
use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener
{
    /**
     * @param WebhookReceived $event
     * @param Price $priceService
     * @return void
     */
    public function handle(WebhookReceived $event, Price $priceService): void
    {
        $price = $event->payload['data']['object'];

        match ($event->payload['type']) {
            'price.created', 'price.updated' => $priceService->put($price),
            'price.deleted' => $priceService->delete($price),
            default => null,
        };
    }
}
