<?php

namespace App\View\Components;

use App\Models\Message;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Chat extends Component
{
    public Collection $chat;

    /**
     * @param string $channel
     */
    public function __construct(public string $channel)
    {
        $messages = Message::query()
            ->where('channel', $channel)
            ->limit(30)
            ->get();

        $this->chat = $messages->groupBy(
            fn (Message $message) => $message->created_at->format('Y-m-d')
        );
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.chat');
    }
}
