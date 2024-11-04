<?php

declare(strict_types=1);

use App\Services\MetaData;
use GuzzleHttp\Promise\RejectedPromise;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

mutates(MetaData::class);

it('returns the cached meta data if it exists', function () {
    $url = 'https://laravel.com';
    $cacheKey = Str::of($url)->slug()->prepend('preview_')->value();
    $cachedData = collect([
        'title' => 'Laravel - The PHP Framework For Web Artisans',
        'description' => 'Laravel is a PHP web application framework with expressive, elegant syntax. We’ve already laid the foundation — freeing you to create without sweating the small things.',
        'keywords' => 'artisan, laravel, php, web, framework, taylor otwell',
        'type' => 'website',
        'url' => 'https://laravel.com/',
        'image' => 'https://laravel.com/img/og-image.jpg',
    ]);

    Http::fake([
        $url => Http::response('
            <html>
                <head>
                    <meta name="title" content="Laravel - The PHP Framework For Web Artisans">
                    <meta name="description" content="Laravel is a PHP web application framework with expressive, elegant syntax. We&rsquo;ve already laid the foundation &mdash; freeing you to create without sweating the small things.">
                    <meta name="keywords" content="artisan, laravel, php, web, framework, taylor otwell">
                    <meta property="og:type" content="website">
                    <meta property="og:url" content="https://laravel.com/">
                    <meta property="og:image" content="https://laravel.com/img/og-image.jpg">
                </head>
            </html>
        ', 200),
    ]);

    $service = new MetaData($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->toArray())->toBe($cachedData->toArray());
});

it('gets the youtube oembed data', function () {
    $url = 'https://youtu.be/emMYyeBfYlM';
    $cacheKey = Str::of($url)->slug()->prepend('preview_')->value();

    Http::fake([
        $url => Http::response('
            <html>
                <head>
                    <meta property="og:title" content="Migrating Brent&rsquo;s PHPUnit Test Suite to Pest">
                    <meta property="og:type" content="video">
                    <meta property="og:url" content="https://www.youtube.com/watch?v=emMYyeBfYlM">
                    <meta property="og:image" content="https://i.ytimg.com/vi/emMYyeBfYlM/maxresdefault.jpg">
                    <meta property="og:site_name" content="YouTube">
                </head>
            </html>
        ', 200),
        'www.youtube.com/oembed?url=*' => Http::response([
            'title' => 'Migrating Brent’s PHPUnit Test Suite to Pest',
            'author_name' => 'Nuno Maduro',
            'author_url' => 'https://www.youtube.com/@nunomaduro',
            'type' => 'video',
            'height' => 113,
            'width' => 200,
            'version' => '1.0',
            'provider_name' => 'YouTube',
            'provider_url' => 'https://www.youtube.com/',
            'thumbnail_height' => 360,
            'thumbnail_width' => 480,
            'thumbnail_url' => 'https://i.ytimg.com/vi/emMYyeBfYlM/hqdefault.jpg',
            'html' => '<iframe width="200" height="113" src="https://www.youtube.com/embed/emMYyeBfYlM?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen title="Migrating Brent’s PHPUnit Test Suite to Pest"></iframe>',
        ], 200),
    ]);

    $service = new MetaData($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->get('title'))->toBe('Migrating Brent’s PHPUnit Test Suite to Pest')
        ->and($data->get('type'))->toBe('video')
        ->and($data->has('html'))->toBeTrue();
});

it('gets the vimdeo oembed data', function () {
    $url = 'https://vimeo.com/76979871';
    $cacheKey = Str::of($url)->slug()->prepend('preview_')->value();

    Http::fake([
        $url => Http::response('
            <html>
                <head>
                    <meta property="og:title" content="The Long Goodbye">
                    <meta property="og:type" content="video">
                    <meta property="og:url" content="https://vimeo.com/76979871">
                    <meta property="og:image" content="https://i.vimeocdn.com/video/449392997_1280.jpg">
                    <meta property="og:site_name" content="Vimeo">
                </head>
            </html>
        ', 200),
        'vimeo.com/oembed?url=*' => Http::response([
            'title' => 'The Long Goodbye',
            'author_name' => 'Ane Brun',
            'author_url' => 'https://vimeo.com/user1957130',
            'type' => 'video',
            'height' => 281,
            'width' => 500,
            'version' => '1.0',
            'provider_name' => 'Vimeo',
            'provider_url' => 'https://vimeo.com/',
            'thumbnail_height' => 360,
            'thumbnail_width' => 640,
            'thumbnail_url' => 'https://i.vimeocdn.com/video/449392997_1280.jpg',
            'html' => '<iframe src="https://player.vimeo.com/video/76979871" width="500" height="281" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="The Long Goodbye"></iframe>',
        ], 200),
    ]);

    $service = new MetaData($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->get('type'))->toBe('video')
        ->and($data->has('html'))->toBeTrue();
});

it('returns an empty collection if the HTTP request fails', function () {
    $url = 'https://aurlthatdoesnotexist.com';

    Http::fake([
        $url => Http::response('', 404),
    ]);

    $service = new MetaData($url);
    $data = $service->fetch();

    expect($data->isEmpty())->toBeTrue();
});

it('returns an empty collection if a ConnectionException is thrown', function () {
    $url = 'https://aurlthatdoesnotexist.com';

    Http::fake([
        $url => fn ($request) => new RejectedPromise(new ConnectionException('Connection error')),
    ]);

    $service = new MetaData($url);
    $data = $service->fetch();

    expect($data->isEmpty())->toBeTrue();

    $url = 'https://youtu.be/emMYyeBfYlM';

    Http::fake([
        $url => Http::response('
            <html>
                <head>
                    <meta property="og:title" content="Migrating Brent&rsquo;s PHPUnit Test Suite to Pest">
                    <meta property="og:type" content="video">
                    <meta property="og:url" content="https://www.youtube.com/watch?v=emMYyeBfYlM">
                    <meta property="og:image" content="https://i.ytimg.com/vi/emMYyeBfYlM/maxresdefault.jpg">
                    <meta property="og:site_name" content="YouTube">
                </head>
            </html>
        ', 200),
        'www.youtube.com/oembed?url=*' => fn ($request) => new RejectedPromise(new ConnectionException('Connection error')),
    ]);

    $service = new MetaData($url);
    $data = $service->fetch();

    expect($data->has('html'))->toBeFalse();
});
