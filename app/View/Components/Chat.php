<?php

namespace App\View\Components;

use App\Models\Message;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Chat extends Component
{
    /**
     * @param string $channel
     */
    public function __construct(public string $channel) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.chat');
    }
}
