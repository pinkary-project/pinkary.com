<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('validates url and returns open graph data', function () {
    Http::fake([
        'https://example.com' => Http::response('
                <html>
                    <head>
                        <meta property="og:title" content="Example Title">
                        <meta property="og:type" content="website">
                        <meta property="og:url" content="https://example.com">
                        <meta property="og:image" content="https://example.com/image.jpg">
                    </head>
                </html>
            ', 200),
    ]);

    $response = $this->postJson(route('opengraph.validate'), [
        'type' => 'url',
        'input' => 'https://example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'metadata' => [
                'title' => 'Example Title',
                'type' => 'website',
                'url' => 'https://example.com',
                'image' => 'https://example.com/image.jpg',
            ],
        ]);
});

it('handles invalid url input', function () {
    $response = $this->postJson(route('opengraph.validate'), [
        'type' => 'url',
        'input' => 'invalid-url',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['input']);
});
