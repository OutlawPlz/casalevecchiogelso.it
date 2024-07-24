<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocalePreferenceController extends Controller
{
    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var ?User $authUser */
        $authUser = $request->user();

        $locale = $request->validate(self::rules())['locale'];

        $authUser
            ? $authUser->update(['locale' => $locale])
            : $request->session()->put('locale', $locale);

        return redirect()->back();
    }

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        $availableLocales = config('app.available_locales');

        return [
            'locale' => ['required', Rule::in($availableLocales)],
        ];
    }
}
