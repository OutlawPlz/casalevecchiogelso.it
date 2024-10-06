<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\TokenAuthenticationController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LocalePreferenceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationFeedController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationStatusController;
use App\Http\Controllers\StripeController;
use App\Models\Message;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;

Route::get('/', function () {
    return view('welcome');
});

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

    /* ----- Checkout ----- */
    Route::post('/checkout', CheckoutController::class)->name('checkout');

    /* ----- Message ----- */
    Route::get('/reservations/{reservation:ulid}/messages', [MessageController::class, 'index'])->name('message.index')
        ->can('viewAny', [Message::class, 'reservation']);
    Route::post('/reservations/{reservation:ulid}/messages', [MessageController::class, 'store'])->name('message.store')
        ->can('create', [Message::class, 'reservation']);
    Route::get('/reservations/{reservation:ulid}/messages/{message}', [MessageController::class, 'show'])->name('message.show')
        ->can('view', [Message::class, 'reservation']);
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

Route::get('/test', function (\Stripe\StripeClient $stripe) {
    return $stripe->paymentIntents->retrieve('pi_3Q6vcBAKSJP4UmE20JSLSegf', [
        'expand' => ['latest_charge.balance_transaction'],
    ]);
});
