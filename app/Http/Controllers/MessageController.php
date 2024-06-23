<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    /**
     * @param Request $request
     * @param Reservation $reservation
     * @return array
     */
    public function store(Request $request, Reservation $reservation): array
    {
        $messages = $reservation->messages ?? [];

        $message = $request->validate(self::rules());

        $message += [
            'ulid' => Str::ulid(),
            'email' => $request->user()->email,
            'created_at' => now(),
        ];

        $messages[] = $message;

        $reservation->update(['messages' => $messages]);

        return $message;
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'message' => ['required', 'string'],
        ];
    }
}
