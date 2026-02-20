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

    expect($renderer->render($message, ['language' => 'en']))->toBe('Hello world');
});

it('renders markdown content when no language is requested', function () {
    $message = Message::factory()->create([
        'content' => ['raw' => '**Bold text**'],
    ]);

    $renderer = app(MessageRenderer::class);

    expect($renderer->render($message))->toContain('<strong>Bold text</strong>');
});

it('translates content and persists it using DeepL', function () {
    $textResult = Mockery::mock(TextResult::class);
    $textResult->text = '<p>Translated text</p>';

    $this->deepl->shouldReceive('translateText')
        ->once()
        ->andReturn($textResult);

    $message = Message::factory()->create([
        'content' => ['raw' => 'Testo originale'],
    ]);

    $renderer = app(MessageRenderer::class);
    $result = $renderer->render($message, ['language' => 'en']);

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
    $result = $renderer->render($message, ['language' => 'en']);

    expect($result)->toContain('Testo originale');
});
