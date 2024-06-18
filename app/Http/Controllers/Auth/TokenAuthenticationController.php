<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TokenLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class TokenAuthenticationController extends Controller
{
    /**
     * @param Request $request
     * @return void
     */
    public function create(Request $request): void
    {
        $attributes = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'required|max:255',
        ]);

        $signedUrl = URL::temporarySignedRoute('auth.token', now()->addHour(), $attributes);

        Notification::send(new User($attributes), new TokenLogin($signedUrl));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->hasValidSignature()) abort(401);

        /** @var User $user */
        $user = User::query()->firstOrNew(['email' => $request->get('email')]);

        if (! $user->exists) $user->forceFill([
            'name' => $request->get('name'),
            'password' => Hash::make(Str::password()),
            'email_verified_at' => now(),
            'provider' => 'email',
        ]);

        $user->save();

        Auth::login($user);

        return redirect('/');
    }
}
