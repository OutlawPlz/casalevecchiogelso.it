<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\TokenAuthenticationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationQuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('reservations/quote', ReservationQuoteController::class)->name('reservation.quote');

require __DIR__.'/auth.php';

Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('social.callback');

Route::post('auth/token', [TokenAuthenticationController::class, 'create']);
Route::get('auth/token', [TokenAuthenticationController::class, 'store'])->name('auth.token');
