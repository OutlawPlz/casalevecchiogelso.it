<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Chat extends Component
{
    public array $templates = [];

    public function __construct(public string $channel) {}

    public function render(): View
    {
        return view('components.chat');
    }
}
