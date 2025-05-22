<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\TokenAuthenticationController;
use App\Http\Controllers\BillingPortalController;
use App\Http\Controllers\ChangeRequest\ApproveChangeRequestController;
use App\Http\Controllers\ChangeRequest\CancelChangeRequestController;
use App\Http\Controllers\ChangeRequest\ChangeRequestController;
use App\Http\Controllers\ChangeRequest\RejectChangeRequestController;
use App\Http\Controllers\LocalePreferenceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reservation\ApproveReservationController;
use App\Http\Controllers\Reservation\CancelReservationController;
use App\Http\Controllers\Reservation\RejectReservationController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationFeedController;
use App\Http\Controllers\StripeController;
use App\Models\Message;
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
    Route::get('/reservations/{reservation:ulid}', [ReservationController::class, 'show'])
        ->name('reservation.show')
        ->can('view', 'reservation');
    Route::get('/reservations/{reservation:ulid}/feed', ReservationFeedController::class)
        ->name('reservation.feed')
        ->can('viewAny', [Activity::class, 'reservation']);
    Route::post('/reservations/{reservation:ulid}/approve', ApproveReservationController::class)
        ->name('reservation.approve');
    Route::get('/reservations/{reservation:ulid}/cancel', [CancelReservationController::class, 'show'])
        ->name('reservation.cancellation_form')
        ->can('cancel', 'reservation');
    Route::post('/reservations/{reservation:ulid}/cancel', [CancelReservationController::class, 'store'])
        ->name('reservation.cancel')
        ->can('cancel', 'reservation');
    Route::post('/reservation/{reservation:ulid}/reject', RejectReservationController::class)
        ->name('reservation.reject')
        ->can('reject', 'reservation');

    /* ----- Change Request ----- */
    Route::scopeBindings()->group(function () {
        Route::get('/reservations/{reservation:ulid}/change-requests/{changeRequest}', [ChangeRequestController::class, 'show'])
            ->name('change_request.show')
            ->can('view', 'changeRequest');
        Route::get('/reservations/{reservation:ulid}/change-requests/create', [ChangeRequestController::class, 'create'])
            ->name('change_request.create')
            ->can('create', 'changeRequest');
        Route::post('/reservations/{reservation:ulid}/change-requests', [ChangeRequestController::class, 'store'])
            ->name('change_request.store')
            ->can('create', 'changeRequest');
        Route::post('/reservations/{reservation:ulid}/change-requests/{changeRequest}/approve', ApproveChangeRequestController::class)
            ->name('change_request.approve')
            ->can('approve', 'changeRequest');
        Route::post('/reservations/{reservation:ulid}/change-requests/{changeRequest}/reject', RejectChangeRequestController::class)
            ->name('change_request.reject')
            ->can('reject', 'changeRequest');
        Route::post('/reservations/{reservation:ulid}/change-requests/{changeRequest}/cancel', CancelChangeRequestController::class)
            ->name('change_request.cancel')
            ->can('cancel', 'changeRequest');
    });

    /* ----- Message ----- */
    Route::get('/reservations/{reservation:ulid}/messages', [MessageController::class, 'index'])
        ->name('message.index')
        ->can('viewAny', [Message::class, 'reservation']);
    Route::post('/reservations/{reservation:ulid}/messages', [MessageController::class, 'store'])
        ->name('message.store')
        ->can('create', [Message::class, 'reservation']);
    Route::get('/reservations/{reservation:ulid}/messages/{message}', [MessageController::class, 'show'])
        ->name('message.show')
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
    $user = \App\Models\User::query()->first();

    return $user->payments->sum('amountPaid');

//    return $stripe->paymentIntents->retrieve(
//        'pi_3RMEFLAKSJP4UmE20jY687Vr',
//        ['expand' => ['latest_charge.balance_transaction', 'latest_charge.refunds']]
//    );

    return (new \App\Actions\Charge)($user, 1000, ['payment_method' => 'pm_card_visa']);
});
