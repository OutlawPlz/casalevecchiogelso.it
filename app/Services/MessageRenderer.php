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

    public function render(Message $message, string $language): string
    {
        return $this->renderMany([$message], $language)[0];
    }

    /**
     * @param  string[]  $contents
     * @return string[]
     */
    protected function translate(array $contents, string $language): array
    {
        try {
            $translatedContents = $this->translator->translateText($contents, null, $language);
        } catch (DeepLException $exception) {
            report($exception);
        }

        if (! isset($translatedContents)) {
            return $contents;
        }

        return array_map(
            fn ($translated, $original) => strncasecmp($translated->detectedSourceLang, $language, 2)
                ? $translated->text
                : $original,
            $translatedContents,
            $contents
        );
    }

    /**
     * @param  array<Message>  $messages
     */
    public function renderMany(array $messages, string $language): array
    {
        $rendered = [];

        $toRender = [];

        foreach ($messages as $key => $message) {
            if (array_key_exists($language, $message->content)) {
                $rendered[$key] = $message->content[$language];

                continue;
            }

            $toRender[$key] = is_template($message)
                ? $this->renderTemplate($message, [])
                : $this->renderContent($message);
        }

        if (! $toRender) {
            return $rendered;
        }

        $translated = array_combine(
            array_keys($toRender),
            $this->translate($toRender, $language)
        );

        // SQLite's upsert requires all NOT NULL columns in the INSERT clause,
        // even when the row already exists and only the UPDATE clause runs.
        $data = [];

        foreach ($translated as $key => $content) {
            $data[] = [
                'id' => $messages[$key]->id,
                'channel' => $messages[$key]->channel,
                'content' => json_encode([$language => $content] + $messages[$key]->content),
            ];
        }

        Message::query()->upsert($data, uniqueBy: ['id'], update: ['content']);

        return $rendered + $translated;
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
