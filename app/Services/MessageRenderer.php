<?php

namespace App\Services;

use App\Models\Message;
use Google\Cloud\Core\Exception\ServiceException;
use Illuminate\Support\Str;

class MessageRenderer
{
    public function __construct(protected GoogleTranslate $translator) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function render(Message $message, array $data = []): string
    {
        $language = $data['language'] ?? '';

        if (array_key_exists($language, $message->content)) {
            return $message->content[$language];
        }

        $isTemplate = str_starts_with($message->content['raw'], '/blade');

        /** @uses MessageRenderer::renderContent() */
        /** @uses MessageRenderer::renderTemplate() */
        $render = $isTemplate ? 'renderTemplate' : 'renderContent';

        /** @var string $renderedContent */
        $renderedContent = $this->$render($message, $data);

        if (! $language) {
            return $renderedContent;
        }

        try {
            $content = $message->content;

            $content[$language] = $this->translator->translate($renderedContent, ['target' => $language])[0]['text'];

            $message->update(['content' => $content]);

            $renderedContent = $content[$language];
        } catch (ServiceException $exception) {
            report($exception);
        }

        return $renderedContent;
    }

    protected function renderContent(Message $message, array $data): string
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
            return 'Not found';
        }

        return view("messages.$template", $data)->render();
    }
}
