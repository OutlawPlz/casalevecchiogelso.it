<?php

namespace App\Services;

use DeepL\DeepLException;
use DeepL\TextResult;
use DeepL\Translator as DeepLClient;

class DeepL
{
    protected DeepLClient $client;

    /**
     * @throws DeepLException
     */
    public function __construct()
    {
        $this->client = new DeepLClient(config('services.deepl.api_key'));
    }

    /**
     * @throws DeepLException
     */
    public function translate(string|array $text, string $targetLang, ?string $sourceLang = null): TextResult|array
    {
        return $this->client->translateText($text, $sourceLang, $targetLang);
    }

    /**
     * @return array{code: string, name: string}[]
     */
    public static function languages(): array
    {
        return [
            ['code' => 'ar', 'name' => 'العربية'],
            ['code' => 'bg', 'name' => 'Български'],
            ['code' => 'cs', 'name' => 'Čeština'],
            ['code' => 'da', 'name' => 'Dansk'],
            ['code' => 'de', 'name' => 'Deutsch'],
            ['code' => 'el', 'name' => 'Ελληνικά'],
            ['code' => 'en-GB', 'name' => 'English (British)'],
            ['code' => 'en-US', 'name' => 'English (American)'],
            ['code' => 'es', 'name' => 'Español'],
            ['code' => 'et', 'name' => 'Eesti'],
            ['code' => 'fi', 'name' => 'Suomi'],
            ['code' => 'fr', 'name' => 'Français'],
            ['code' => 'hu', 'name' => 'Magyar'],
            ['code' => 'id', 'name' => 'Bahasa Indonesia'],
            ['code' => 'it', 'name' => 'Italiano'],
            ['code' => 'ja', 'name' => '日本語'],
            ['code' => 'ko', 'name' => '한국어'],
            ['code' => 'lt', 'name' => 'Lietuvių'],
            ['code' => 'lv', 'name' => 'Latviešu'],
            ['code' => 'nb', 'name' => 'Norsk bokmål'],
            ['code' => 'nl', 'name' => 'Nederlands'],
            ['code' => 'pl', 'name' => 'Polski'],
            ['code' => 'pt-BR', 'name' => 'Português (Brasil)'],
            ['code' => 'pt-PT', 'name' => 'Português (Portugal)'],
            ['code' => 'ro', 'name' => 'Română'],
            ['code' => 'ru', 'name' => 'Русский'],
            ['code' => 'sk', 'name' => 'Slovenčina'],
            ['code' => 'sl', 'name' => 'Slovenščina'],
            ['code' => 'sv', 'name' => 'Svenska'],
            ['code' => 'tr', 'name' => 'Türkçe'],
            ['code' => 'uk', 'name' => 'Українська'],
            ['code' => 'zh', 'name' => '简体中文'],
        ];
    }
}
