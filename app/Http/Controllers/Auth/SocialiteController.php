<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController
{
    protected array $providers = ['google'];

    /**
     * @param string $provider
     * @return RedirectResponse
     */
    public function redirect(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->providers)) abort(404);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * @param string $provider
     * @return RedirectResponse
     */
    public function callback(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->providers)) abort(404);

        // TODO: Handle HTTP errors.
        $googleUser = Socialite::driver($provider)->user();

        /** @var User $user */
        $user = User::query()->firstOrNew(['email' => $googleUser->getEmail()]);

        if (! $user->exists) $user->forceFill([
            'name' => $googleUser->getName(),
            'password' => Hash::make(Str::password(24)),
            'email_verified_at' => now(),
            'provider' => $provider,
            'provider_id' => $googleUser->getId(),
        ]);

        $user->save();

        Auth::login($user);

        return redirect('/');
    }
}
