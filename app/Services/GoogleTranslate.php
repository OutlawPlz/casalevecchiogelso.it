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
        if (is_string($content)) {
            $content = [$content];
        }

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
        if (is_string($content)) {
            $content = [$content];
        }

        return $this->client->detectLanguageBatch($content, $options);
    }

    /**
     * @return array{code: string, name: string}[]
     */
    public static function languages(): array
    {
        return [
            ['code' => 'az', 'name' => 'Azərbaycan dili',],
            ['code' => 'id', 'name' => 'Bahasa Indonesia',],
            ['code' => 'bs', 'name' => 'Bosanski',],
            ['code' => 'ca', 'name' => 'Català',],
            ['code' => 'cs', 'name' => 'Čeština',],
            ['code' => 'sr', 'name' => 'Crnogorski',],
            ['code' => 'da', 'name' => 'Dansk',],
            ['code' => 'de', 'name' => 'Deutsch',],
            ['code' => 'et', 'name' => 'Eesti',],
            ['code' => 'en', 'name' => 'English',],
            ['code' => 'es', 'name' => 'Español',],
            ['code' => 'fr', 'name' => 'Français',],
            ['code' => 'ga', 'name' => 'Gaeilge',],
            ['code' => 'hr', 'name' => 'Hrvatski',],
            ['code' => 'xh', 'name' => 'isiXhosa',],
            ['code' => 'zu', 'name' => 'isiZulu',],
            ['code' => 'is', 'name' => 'Íslenska',],
            ['code' => 'it', 'name' => 'Italiano',],
            ['code' => 'sw', 'name' => 'Kiswahili',],
            ['code' => 'lv', 'name' => 'Latviešu',],
            ['code' => 'lt', 'name' => 'Lietuvių',],
            ['code' => 'hu', 'name' => 'Magyar',],
            ['code' => 'mt', 'name' => 'Malti',],
            ['code' => 'ms', 'name' => 'Melayu',],
            ['code' => 'nl', 'name' => 'Nederlands',],
            ['code' => 'no', 'name' => 'Norsk',],
            ['code' => 'pl', 'name' => 'Polski',],
            ['code' => 'pt', 'name' => 'Português',],
            ['code' => 'ro', 'name' => 'Română',],
            ['code' => 'sq', 'name' => 'Shqip',],
            ['code' => 'sk', 'name' => 'Slovenčina',],
            ['code' => 'sl', 'name' => 'Slovenščina',],
            ['code' => 'sr', 'name' => 'Srpski',],
            ['code' => 'fi', 'name' => 'Suomi',],
            ['code' => 'sv', 'name' => 'Svenska',],
            ['code' => 'tl', 'name' => 'Tagalog',],
            ['code' => 'vi', 'name' => 'Tiếng Việt',],
            ['code' => 'tr', 'name' => 'Türkçe',],
            ['code' => 'el', 'name' => 'Ελληνικά',],
            ['code' => 'bg', 'name' => 'Български',],
            ['code' => 'mk', 'name' => 'Македонски',],
            ['code' => 'ru', 'name' => 'Русский',],
            ['code' => 'uk', 'name' => 'Українська',],
            ['code' => 'ka', 'name' => 'ქართული',],
            ['code' => 'hy', 'name' => 'Հայերեն',],
            ['code' => 'he', 'name' => 'עברית',],
            ['code' => 'ar', 'name' => 'العربية',],
            ['code' => 'hi', 'name' => 'हिन्दी',],
            ['code' => 'th', 'name' => 'ไทย',],
            ['code' => 'ko', 'name' => '한국어',],
            ['code' => 'ja', 'name' => '日本語',],
            ['code' => 'zh', 'name' => '简体中文',],
        ];
    }
}
