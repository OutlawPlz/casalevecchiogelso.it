<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\View\Component;

class LanguageSwitcher extends Component
{
    /** @var array<string, string> */
    public array $availableLocales = [];

    public string $locale = '';

    public function __construct()
    {
        $this->availableLocales = config('app.available_locales');
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('components.language-switcher');
    }
}
