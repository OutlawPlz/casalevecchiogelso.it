<?php

use App\Enums\ChangeRequestStatus;
use App\Http\Controllers\StripeController;
use App\Models\ChangeRequest;
use Spatie\Activitylog\Models\Activity;
use Stripe\Event;

test('charge.refunded webhook logs confirmation', function () {
    $changeRequest = ChangeRequest::factory()
        ->create(['status' => ChangeRequestStatus::APPROVED]);

    // Create Stripe Event object
    $event = Event::constructFrom([
        'id' => 'evt_test',
        'type' => 'charge.refunded',
        'data' => [
            'object' => [
                'id' => 'ch_test_123',
                'object' => 'charge',
                'payment_intent' => 'pi_test_123',
                'refunds' => [
                    'object' => 'list',
                    'data' => [
                        [
                            'id' => 're_test_123',
                            'object' => 'refund',
                            'amount' => 5000,
                            'status' => 'succeeded',
                            'metadata' => [
                                'reservation' => $changeRequest->reservation->ulid,
                                'change_request' => $changeRequest->ulid,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    // Call the webhook handler directly
    $controller = new StripeController;
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('handleChargeRefunded');
    $method->setAccessible(true);
    $method->invoke($controller, $event);

    // Verify webhook only logs (doesn't change status)
    expect($changeRequest->refresh()->status)
        ->toBe(ChangeRequestStatus::APPROVED)
        ->and(Activity::query()
            ->where('subject_id', $changeRequest->reservation->id)
            ->where('description', 'like', '%Refund%confirmed%')
            ->exists())
        ->toBeTrue();
});
