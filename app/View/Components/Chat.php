<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Chat extends Component
{
    public array $templates = [];

    /**
     * @param string $channel
     */
    public function __construct(public string $channel) {}

    /**
     * @return View
     */
    public function render(): View
    {
        return view('components.chat');
    }
}
