<?php

namespace App\Services;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Translate\V2\TranslateClient;

class GoogleTranslate
{
    protected TranslateClient $client;

    /**
     * @throws GoogleException
     */
    public function __construct()
    {
        $headers = ['headers' => ['referer' => config('app.url')]];

        $this->client = new TranslateClient([
            'key' => config('services.google.api_secret'),
            'restOptions' => $headers,
        ]);
    }

    /**
     * @param  array|string  $content
     * @param  array  $options
     * @return array<int, array{source: string, input: string, text: string}>
     * @throws ServiceException
     */
    public function translate(array|string $content, array $options = []): array
    {
        if (is_string($content)) $content = [$content];

        return $this->client->translateBatch($content, $options);
    }

    /**
     * @param  array|string  $content
     * @param  array  $options
     * @return array<int, array{languageCode: string, input: string, confidence: float}>
     * @throws ServiceException
     */
    public function detectLanguage(array|string $content, array $options = []): array
    {
        if (is_string($content)) $content = [$content];

        return $this->client->detectLanguageBatch($content, $options);
    }
}
