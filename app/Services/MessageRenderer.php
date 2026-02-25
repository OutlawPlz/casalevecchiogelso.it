<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Reservation;
use DeepL\DeepLException;
use DeepL\Translator as DeepLClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function App\Helpers\is_template;

class MessageRenderer
{
    public function __construct(protected DeepLClient $translator) {}

    /**
     * @param  array{language: string, reservation: Reservation}  $data
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

        // If $language is an empty string, we're showing the
        // message to its author, no translation needed.
        if (! $language) {
            return $renderedContent;
        }

        $renderedContent = $this->translate($renderedContent, $language);

        $message->update(["content->$language" => $renderedContent]);

        return $renderedContent;
    }

    protected function translate(string $text, string $target): string
    {
        try {
            $translatedContent = $this->translator->translateText($text, null, $target);
        } catch (DeepLException $exception) {
            report($exception);
        }

        if (! isset($translatedContent) || ! strncasecmp($translatedContent->detectedSourceLang, $target, 2)) {
            return $text;
        }

        return $translatedContent->text;
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
