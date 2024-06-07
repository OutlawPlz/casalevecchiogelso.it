<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Services\Calendar;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', fn(Calendar $calendar) => view('dashboard', ['unavailableDates' => $calendar->unavailableDates()]))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::post('/reservations', [ReservationController::class, 'store'])->name('reservation.store');
Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservation.create');
Route::get('/reservations/{reservation:ulid}', [ReservationController::class, 'show'])->name('reservation.show');
Route::patch('/reservations/{reservation:ulid}', [ReservationController::class, 'update'])->name('reservation.update');

Route::post('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::get('/checkout', [CheckoutController::class, 'success'])->name('checkout.success');

Route::get('/test', function (\Illuminate\Http\Request $request) {
    return \Laravel\Cashier\Cashier::stripe()->prices->all()['data'];
});
