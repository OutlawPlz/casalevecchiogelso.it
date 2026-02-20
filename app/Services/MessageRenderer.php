<?php

namespace App\Services;

use App\Models\Message;
use DeepL\DeepLException;
use Illuminate\Support\Str;
use function App\Helpers\is_template;

class MessageRenderer
{
    public function __construct(protected DeepL $translator) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function render(Message $message, array $data = []): string
    {
        $language = $data['language'] ?? '';

        if (array_key_exists($language, $message->content)) {
            return $message->content[$language];
        }

        $renderedContent = is_template($message)
            ? $this->renderContent($message)
            : $this->renderTemplate($message, $data);

        if (! $language) {
            return $renderedContent;
        }

        try {
            $content = $message->content;

            $content[$language] = $this->translator->translate($renderedContent, $language)->text;

            $message->update(['content' => $content]);

            $renderedContent = $content[$language];
        } catch (DeepLException $exception) {
            report($exception);
        }

        return $renderedContent;
    }

    protected function renderContent(Message $message): string
    {
        return Str::markdown($message->content['raw'], [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    protected function renderTemplate(Message $message, array $data): string
    {
        $template = explode(':', $message->content['raw'], 2)[1] ?? '';

        if (! $template) {
            return "Not found {$message->content['raw']}";
        }

        return view("messages.$template", $data)->render();
    }
}
