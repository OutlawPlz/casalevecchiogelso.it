<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\TokenAuthenticationController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LocalePreferenceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationQuoteController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\StripeController;
use Google\Cloud\Translate\V2\TranslateClient;
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
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservation.store');
    Route::post('/reservations/{reservation:ulid}', [ReservationController::class, 'update'])->name('reservation.update');
    Route::get('/reservations/{reservation:ulid}', [ReservationController::class, 'show'])->name('reservation.show');

    /* ----- Checkout ----- */
    Route::post('/checkout', CheckoutController::class)->name('checkout');

    /* ----- Message ----- */
    Route::get('/reservations/{reservation:ulid}/messages', [MessageController::class, 'index'])->name('message.index');
    Route::post('/reservations/{reservation:ulid}/messages', [MessageController::class, 'store'])->name('message.store');
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
