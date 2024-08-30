<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\TokenAuthenticationController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LocalePreferenceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationFeedController;
use App\Http\Controllers\ReservationQuoteController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationStatusController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('reservations/quote', ReservationQuoteController::class)->name('reservation.quote');

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
    Route::post('/reservations/{reservation:ulid}', [ReservationController::class, 'update'])->name('reservation.update');
    Route::get('/reservations/{reservation:ulid}', [ReservationController::class, 'show'])->name('reservation.show');
    Route::post('/reservations/{reservation:ulid}/status', ReservationStatusController::class)->name('reservation.status');
    Route::get('/reservations/{reservation:ulid}/feed', ReservationFeedController::class)->name('reservation.feed');

    /* ----- Checkout ----- */
    Route::post('/checkout', CheckoutController::class)->name('checkout');

    /* ----- Message ----- */
    Route::get('/reservations/{reservation:ulid}/messages', [MessageController::class, 'index'])->name('message.index');
    Route::post('/reservations/{reservation:ulid}/messages', [MessageController::class, 'store'])->name('message.store');
    Route::get('/reservations/{reservation:ulid}/messages/{message}', [MessageController::class, 'show'])->name('message.show');
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
    return $stripe->paymentIntents->retrieve('pi_3PsWK1AKSJP4UmE23u551Jio', [
        'expand' => ['latest_charge.balance_transaction'],
    ]);
});
