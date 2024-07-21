<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Chat extends Component
{
    public array $templates = [
        [
            'label' => 'Ask to pay',
            'template' => 'ask-to-pay',
        ]
    ];

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
