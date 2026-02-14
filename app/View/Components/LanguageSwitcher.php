<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
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

    public function render(): View
    {
        return view('components.language-switcher');
    }
}
