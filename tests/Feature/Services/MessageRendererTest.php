<?php

use App\Models\Message;
use App\Services\MessageRenderer;
use DeepL\TextResult;
use DeepL\Translator as DeepLClient;

beforeEach(function () {
    $this->deepl = Mockery::mock(DeepLClient::class);
    $this->app->instance(DeepLClient::class, $this->deepl);
});

it('returns cached translation when language already exists in content', function () {
    $message = Message::factory()->create([
        'content' => ['raw' => 'Ciao mondo', 'it' => 'Ciao mondo', 'en' => 'Hello world'],
    ]);

    $renderer = app(MessageRenderer::class);

    expect($renderer->render($message, 'en'))->toBe('Hello world');
});

it('renders markdown content and translates it', function () {
    $textResult = Mockery::mock(TextResult::class);
    $textResult->text = '<strong>Testo in grassetto</strong>';
    $textResult->detectedSourceLang = 'en';

    $this->deepl->shouldReceive('translateText')
        ->once()
        ->andReturn([$textResult]);

    $message = Message::factory()->create([
        'content' => ['raw' => '**Bold text**'],
    ]);

    $renderer = app(MessageRenderer::class);

    expect($renderer->render($message, 'it'))->toBe('<strong>Testo in grassetto</strong>');
});

it('translates content and persists it using DeepL', function () {
    $textResult = Mockery::mock(TextResult::class);
    $textResult->text = '<p>Translated text</p>';
    $textResult->detectedSourceLang = 'it';

    $this->deepl->shouldReceive('translateText')
        ->once()
        ->andReturn([$textResult]);

    $message = Message::factory()->create([
        'content' => ['raw' => 'Testo originale'],
    ]);

    $renderer = app(MessageRenderer::class);
    $result = $renderer->render($message, 'en');

    expect($result)->toBe('<p>Translated text</p>');

    $message->refresh();
    expect($message->content['en'])->toBe('<p>Translated text</p>');
});

it('reports exception and returns rendered content when DeepL fails', function () {
    $this->deepl->shouldReceive('translateText')
        ->once()
        ->andThrow(new \DeepL\DeepLException('API error'));

    $message = Message::factory()->create([
        'content' => ['raw' => 'Testo originale'],
    ]);

    $renderer = app(MessageRenderer::class);
    $result = $renderer->render($message, 'en');

    expect($result)->toContain('Testo originale');
});
