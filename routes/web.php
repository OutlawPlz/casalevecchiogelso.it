<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\TokenAuthenticationController;
use App\Http\Controllers\BillingPortalController;
use App\Http\Controllers\ChangeRequest\ChangeRequestController;
use App\Http\Controllers\LocalePreferenceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reservation\ApproveReservationController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationFeedController;
use App\Http\Controllers\ReservationStatusController;
use App\Http\Controllers\StripeController;
use App\Models\Message;
use App\Models\Reservation;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::group([
    'middleware' => ['auth', 'verified']
], function () {
    /* ----- Profile ----- */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* ----- Reservation ----- */
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservation.index');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservation.store');
    Route::get('/reservations/{reservation:ulid}', [ReservationController::class, 'show'])->name('reservation.show')
        ->can('view', 'reservation');
    Route::get('/reservations/{reservation:ulid}/cancel', [ReservationController::class, 'delete'])->name('reservation.delete')
        ->can('view', 'reservation');
    Route::delete('/reservations/{reservation:ulid}', [ReservationController::class, 'destroy'])->name('reservation.destroy')
        ->can('destroy', 'reservation');
    Route::post('/reservations/{reservation:ulid}/status', ReservationStatusController::class)->name('reservation.status')
        ->can('update', 'reservation');
    Route::get('/reservations/{reservation:ulid}/feed', ReservationFeedController::class)->name('reservation.feed')
        ->can('viewAny', [Activity::class, 'reservation']);

    Route::post('/reservations/{reservation:ulid}/approve', ApproveReservationController::class)->name('reservation.approve');

    /* ----- Change Request ----- */
    Route::get('/reservations/{reservation:ulid}/change', [ChangeRequestController::class, 'create'])->name('change_request.create');
    Route::post('/reservations/{reservation:ulid}/change', [ChangeRequestController::class, 'store'])->name('change_request.store');

    /* ----- Message ----- */
    Route::get('/reservations/{reservation:ulid}/messages', [MessageController::class, 'index'])->name('message.index')
        ->can('viewAny', [Message::class, 'reservation']);
    Route::post('/reservations/{reservation:ulid}/messages', [MessageController::class, 'store'])->name('message.store')
        ->can('create', [Message::class, 'reservation']);
    Route::get('/reservations/{reservation:ulid}/messages/{message}', [MessageController::class, 'show'])->name('message.show')
        ->can('view', [Message::class, 'reservation']);

    /* ----- Billing portal ----- */
    Route::post('/billing-portal', BillingPortalController::class)->name('billing_portal');;
});

/* ----- Stripe Webhook ----- */
Route::post('/stripe/webhook', StripeController::class);

require __DIR__.'/auth.php';

/* ----- Socialite ----- */
Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('social.callback');

/* ----- Email token ----- */
Route::post('auth/token', [TokenAuthenticationController::class, 'create']);
Route::get('auth/token', [TokenAuthenticationController::class, 'store'])->name('auth.token');

/* ----- Locale preference ----- */
Route::post('/locale-preference', LocalePreferenceController::class)->name('locale-preference');

Route::get('/test', function (\Illuminate\Http\Request $request, \Stripe\StripeClient $stripe) {

    return $stripe->charges->retrieve('ch_3RMEFLAKSJP4UmE20ggUQT0o', ['expand' => ['balance_transaction']]);

    // return $stripe->refunds->all(['payment_intent' => 'pi_3RL3tbAKSJP4UmE21ylQd6L1']);

    $reservation = Reservation::query()->find(5);

    $changeRequest = \App\Models\ChangeRequest::query()->find(3);

    $reservation->apply($changeRequest)->save();

    $changeRequest->update(['status' => \App\Enums\ChangeRequestStatus::CONFIRMED]);

    return $reservation;

    return (new \App\Actions\RefundGuest)($reservation, 50000);

    $paymentMethod = $reservation->user->paymentMethods()[0];

    return $paymentIntent = $stripe->paymentIntents->create([
        'amount' => $reservation->tot,
        'confirm' => true,
        'off_session' => true,
        'customer' => $reservation->user->stripe_id,
        'payment_method' => $paymentMethod->id,
        'currency' => config('services.stripe.currency'),
        'metadata' => [
            'reservation' => $reservation->ulid,
        ]
    ]);
});
