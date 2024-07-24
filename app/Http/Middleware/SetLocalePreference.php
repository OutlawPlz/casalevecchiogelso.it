<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocalePreference
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ?User $authUser */
        $authUser = $request->user();

        $localePreference = $authUser
            ? $authUser->locale
            : $request->session()->get('locale');

        if ($localePreference) {
            App::setLocale($localePreference);
        }

        return $next($request);
    }
}
