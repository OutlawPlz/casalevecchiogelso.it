<?php

namespace App\Services;

use App\Models\Message;
use Google\Cloud\Core\Exception\ServiceException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MessageRenderer
{
    /**
     * @param  GoogleTranslate  $translator
     */
    public function __construct(protected GoogleTranslate $translator) {}

    /**
     * @param  Message  $message
     * @param  array<string, mixed>  $data
     * @return string
     */
    public function render(Message $message, array $data = []): string
    {
        $isTemplate = str_starts_with($message->content['raw'], '/blade');

        $render = $isTemplate ? 'renderTemplate' : 'renderContent';
        /** @var string $content */
        $content =  $this->$render($message, $data);

        return view(
            'messages.show',
            [
                'message' => $message,
                'content' => $content,
                'isOwner' => $message->user()->is(Auth::user()),
            ]
        )->render();
    }

    /**
     * @param  array|Collection  $messages
     * @param  array<string, mixed>  $data
     * @return string
     */
    public function renderMany(array|Collection $messages, array $data = []): string
    {
        $chat = $messages->groupBy(
            fn (Message $message) => $message->created_at->format('d M')
        );

        return view(
            'messages.index',
            [
                'data' => $data,
                'chat' => $chat,
                'renderer' => $this,
            ]
        )->render();
    }

    /**
     * @param  Message  $message
     * @param  array  $data
     * @return string
     */
    protected function renderContent(Message $message, array $data): string
    {
        $content = $message->content;

        $language = $data['language'] ?? '';

        if (array_key_exists($language, $content)) return $content[$language];

        $renderedContent = Str::markdown($message->content['raw'], [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        if (! $language || $message->user()->is(Auth::user())) return $renderedContent;

        try {
            $content[$language] = $this->translator->translate($renderedContent, ['target' => $language])[0]['text'];

            $message->update(['content' => $content]);

            $renderedContent = $content[$language];
        } catch (ServiceException $exception) {
            report($exception);
        }

        return $renderedContent;
    }

    /**
     * @param  Message  $message
     * @param  array  $data
     * @return string
     */
    protected function renderTemplate(Message $message, array $data): string
    {
        $template = explode(':', $message->content['raw'], 2)[1] ?? '';

        if (! $template) return 'Not found';

        // TODO: Translate content in user language.

        return view("messages.$template", $data)->render();
    }
}
