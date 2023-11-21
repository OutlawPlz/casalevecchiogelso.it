<?php

use App\Http\Controllers\ProfileController;
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

    $response = \Illuminate\Support\Facades\Http::get(env('AIRBNB_ICS_LINK'));

    $body = str_replace("\n ", '', $response->body());

    preg_match_all('/BEGIN:VEVENT(?s).*?END:VEVENT/', $body, $matches);

    $reservations = [];

    foreach ($matches[0] as $match) {
        $event = [];

        $lines = explode("\n", $match);

        foreach ($lines as $line) {
            [$key, $value] = explode(':', $line, 2);

            $event[$key] = $value;
        }

        $reservations[] = [
            'uid' => $event['UID'],
            'start_at' => (new \DateTime($event['DTSTART;VALUE=DATE']))->format('Y-m-d'),
            'end_at' => (new \DateTime($event['DTEND;VALUE=DATE']))->format('Y-m-d'),
            'summary' => $event['DESCRIPTION'] ?? $event['SUMMARY']
        ];
    }

    \Illuminate\Support\Facades\Storage::put(
        'calendar.json',
        json_encode($reservations, JSON_PRETTY_PRINT)
    );
});

Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
