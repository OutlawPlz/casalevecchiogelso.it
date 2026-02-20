<?php

namespace App\Services;

use App\Models\Message;
use DeepL\DeepLException;
use DeepL\Translator as DeepLClient;
use Illuminate\Support\Str;

use function App\Helpers\is_template;

class MessageRenderer
{
    public function __construct(protected DeepLClient $translator) {}

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
            ? $this->renderTemplate($message, $data)
            : $this->renderContent($message);

        if (! $language) {
            return $renderedContent;
        }

        try {
            $renderedContent = $this->translator->translateText($renderedContent, null, $language)->text;

            $message->update(["content->$language" => $renderedContent]);
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
