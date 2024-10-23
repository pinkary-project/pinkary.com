<?php

declare(strict_types=1);

use App\Services\MetaData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

mutates(MetaData::class);

it('fetches and caches meta data for a given URL', function () {
    $url = 'https://example.com';
    $cacheKey = Str::of($url)->slug()->prepend('preview_')->value();
    $mockResponse = <<<'HTML'
    <html>
        <head>
            <meta name="title" content="Example Title">
            <meta name="description" content="Example Description">
            <meta name="og:title" content="Example Title">
            <meta name="og:description" content="Example Description">
        </head>
    </html>
    HTML;

    // Mock the HTTP response
    Http::fake([
        $url => Http::response($mockResponse),
    ]);

    // Ensure the cache is empty
    Cache::shouldReceive('remember')
        ->once()
        ->with($cacheKey, Carbon::now()->addYear(), Mockery::type('callable'))
        ->andReturnUsing(static function ($key, $ttl, $callback) {
            return $callback();
        });

    // Fetch the meta data
    $data = MetaData::fetch($url);

    // Assert the data is as expected
    expect($data->toArray())->toBe([
        'title' => 'Example Title',
        'description' => 'Example Description',
    ]);
});

it('returns an empty collection if the HTTP request fails', function () {
    $url = 'https://example.com';

    Http::fake([
        $url => Http::response(null, 404),
    ]);

    $data = MetaData::fetch($url);

    expect($data->isEmpty())->toBeTrue();
});
