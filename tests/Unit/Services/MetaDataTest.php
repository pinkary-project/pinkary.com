<?php

declare(strict_types=1);

use App\Services\MetaData;
use App\Services\SafeUrlFetcher;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

function metaDataWithFakeDns(string $url, ?Closure $resolveHost = null): MetaData
{
    return new MetaData(
        $url,
        new SafeUrlFetcher($resolveHost ?? static fn (string $host): array => ['93.184.216.34']),
    );
}

/**
 * Mirrors the versioned cache key, so bumping the version surfaces here.
 */
function previewCacheKey(string $url): string
{
    return Str::of($url)->slug()->prepend('preview_v1_')->value();
}

mutates(MetaData::class);

it('returns the cached meta data if it exists', function (): void {
    $url = 'https://laravel.com';
    $cacheKey = previewCacheKey($url);
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
        'https://laravel.com/img/og-image.jpg' => Http::response(''),
    ]);

    $service = metaDataWithFakeDns($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->toArray())->toBe($cachedData->toArray());
});

it('gets the youtube oembed data', function (): void {
    $url = 'https://youtu.be/emMYyeBfYlM';
    $cacheKey = previewCacheKey($url);

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

    $service = metaDataWithFakeDns($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->get('title'))->toBe('Migrating Brent’s PHPUnit Test Suite to Pest')
        ->and($data->get('type'))->toBe('video')
        ->and($data->has('html'))->toBeTrue();
});

it('shows preview card if the tweet has an image or video', function (): void {
    $url = 'https://x.com/enunomaduro/status/1845794776886493291';
    $image = UploadedFile::fake()->image('image.jpg', 1000, 600);
    $imageContents = file_get_contents($image->getRealPath());
    $imagePath = 'https://images.example/image.jpg';
    $cacheKey = previewCacheKey($url);

    Http::fake([
        $url => Http::response('
            <html>
                <head>
                    <meta property="og:site_name" content="X (formerly Twitter)">
                    <meta property="og:url" content="https://x.com/enunomaduro/status/1853213385233502522">
                    <meta property="og:image" content="'.$imagePath.'">
                </head>
            </html>
        ', 200),
        $imagePath => Http::response($imageContents, 200),
        '*' => Http::response(null, 200),
    ]);

    $service = metaDataWithFakeDns($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->get('site_name'))->toBe('X (formerly Twitter)')
        ->and($data->get('url'))->toBe('https://x.com/enunomaduro/status/1853213385233502522')
        ->and($data->get('image'))->toBe($imagePath);
});

it('gets the vimdeo oembed data', function (): void {
    $url = 'https://vimeo.com/76979871';
    $cacheKey = previewCacheKey($url);

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

    $service = metaDataWithFakeDns($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->get('type'))->toBe('video')
        ->and($data->has('html'))->toBeTrue();
});

it('returns an empty collection if the HTTP request fails', function (): void {
    $url = 'https://aurlthatdoesnotexist.com';

    Http::fake([
        $url => Http::response('', 404),
    ]);

    $service = metaDataWithFakeDns($url);
    $data = $service->fetch();

    expect($data->isEmpty())->toBeTrue();
});

it('returns an empty collection if a ConnectionException is thrown', function (): void {
    $url = 'https://aurlthatdoesnotexist.com';

    Http::fake([
        $url => fn ($request): RejectedPromise => new RejectedPromise(new ConnectionException('Connection error')),
    ]);

    $service = metaDataWithFakeDns($url);
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
        'www.youtube.com/oembed?url=*' => fn ($request): RejectedPromise => new RejectedPromise(new ConnectionException('Connection error')),
    ]);

    $service = metaDataWithFakeDns($url);
    $data = $service->fetch();

    expect($data->has('html'))->toBeFalse();
});

it('removes the image from meta data if the image is too small', function (): void {
    $url = 'https://laravel.com';
    $image = UploadedFile::fake()->image('image.jpg', 100, 100);
    $imageContents = file_get_contents($image->getRealPath());
    $imagePath = 'https://images.example/small.jpg';

    Http::fake([
        $url => Http::response('
            <html>
                <head>
                    <meta name="title" content="Laravel - The PHP Framework For Web Artisans">
                    <meta name="description" content="Laravel is a PHP web application framework with expressive, elegant syntax. We’ve already laid the foundation — freeing you to create without sweating the small things.">
                    <meta name="keywords" content="artisan, laravel, php, web, framework, taylor otwell">
                    <meta property="og:type" content="website">
                    <meta property="og:url" content="https://laravel.com/">
                    <meta property="og:image" content="'.$imagePath.'">
                </head>
            </html>
        '),
        $imagePath => Http::response($imageContents, 200),
    ]);

    $service = metaDataWithFakeDns($url);
    $data = $service->fetch();

    expect($data->has('image'))->toBeFalse();
});

it('removes the image from meta data if the image file does not exist', function (): void {
    $url = 'https://laravel.com';
    $imagePath = 'https://images.example/missing.jpg';

    Http::fake([
        $url => Http::response('
            <html>
                <head>
                    <meta name="title" content="Laravel - The PHP Framework For Web Artisans">
                    <meta name="description" content="Laravel is a PHP web application framework with expressive, elegant syntax. We’ve already laid the foundation — freeing you to create without sweating the small things.">
                    <meta name="keywords" content="artisan, laravel, php, web, framework, taylor otwell">
                    <meta property="og:type" content="website">
                    <meta property="og:url" content="https://laravel.com/">
                    <meta property="og:image" content="'.$imagePath.'">
                </head>
            </html>
        '),
        $imagePath => Http::response('', 404),
    ]);

    $service = metaDataWithFakeDns($url);
    $data = $service->fetch();

    expect($data->has('image'))->toBeFalse();
});

it('does not fetch a private open graph image URL', function (): void {
    $url = 'https://public.example/post';
    $requests = [];

    Http::fake([$url => function (Request $request) use (&$requests): mixed {
        $requests[] = $request->url();

        return Http::response('<meta property="og:image" content="http://127.0.0.1/internal.png">', 200);
    }]);
    Http::preventStrayRequests();

    $data = metaDataWithFakeDns($url)->fetch();

    expect($data->has('image'))->toBeFalse()
        ->and($requests)->toBe([$url]);
});

it('has constants for the preview card dimensions', function (): void {
    expect(MetaData::CARD_WIDTH)->toBe(446)
        ->and(MetaData::CARD_HEIGHT)->toBe(251);
});

it('dimensions are 16:9', function (): void {
    expect(round(MetaData::CARD_WIDTH / MetaData::CARD_HEIGHT, 2))
        ->toBe(round(16 / 9, 2));
});

it('handles all exceptions', function (Exception $exception): void {
    $url = 'https://laravel.com';

    Http::fake([
        $url => fn ($request): RejectedPromise => new RejectedPromise($exception),
    ]);

    $service = metaDataWithFakeDns($url);
    $data = $service->fetch();

    expect($data->isEmpty())->toBeTrue();
})->with([
    'connection' => fn (): Exception => new ConnectionException('Connection error'),
    'malformed uri' => fn (): Exception => new MalformedUriException('Malformed URI'),
    'http client' => fn (): Exception => new HttpClientException('Not Found'),
    'transfer' => fn (): Exception => new TransferException('Transfer error', new Psr7Request('GET', 'https://laravel.com')),
]);

it('handles empty content', function (): void {
    $url = 'https://laravel.com';

    Http::fake([
        $url => Http::response('', 200),
    ]);

    $service = metaDataWithFakeDns($url);
    $data = $service->fetch();

    expect($data->isEmpty())->toBeTrue();
});

it('bounds page, image and twitter requests with a short timeout', function (): void {
    $url = 'https://x.com/example/status/123';
    $image = UploadedFile::fake()->image('timeout.jpg', 1000, 600);
    $imageContents = file_get_contents($image->getRealPath());
    $imagePath = 'https://images.example/timeout.jpg';

    $html = '
        <html>
            <head>
                <meta property="og:site_name" content="X (formerly Twitter)">
                <meta property="og:url" content="'.$url.'">
                <meta property="og:image" content="'.$imagePath.'">
            </head>
        </html>
    ';

    /** @var array<int, mixed> $timeouts */
    $timeouts = [];

    Http::fake([
        $url => function (Request $request, array $options) use (&$timeouts, $html): mixed {
            $timeouts[] = $options['timeout'] ?? null;

            return Http::response($html, 200);
        },
        $imagePath => function (Request $request, array $options) use (&$timeouts, $imageContents): mixed {
            $timeouts[] = $options['timeout'] ?? null;

            return Http::response($imageContents, 200);
        },
        '*' => function (Request $request, array $options) use (&$timeouts): mixed {
            $timeouts[] = $options['timeout'] ?? null;

            return Http::response(null, 200);
        },
    ]);
    Http::preventStrayRequests();

    $data = metaDataWithFakeDns($url)->fetch();

    expect($data->get('image'))->toBe($imagePath)
        ->and($timeouts)->not->toBeEmpty()
        ->and($timeouts)->each->toBe(3);
});

it('bounds oembed requests with a short timeout', function (): void {
    $url = 'https://youtu.be/dQw4w9WgXcQ';

    /** @var array<int, mixed> $timeouts */
    $timeouts = [];

    Http::fake([
        'www.youtube.com/oembed?url=*' => function (Request $request, array $options) use (&$timeouts): mixed {
            $timeouts[] = $options['timeout'] ?? null;

            return Http::response(['title' => 'Some video', 'type' => 'video'], 200);
        },
    ]);
    Http::preventStrayRequests();

    $data = metaDataWithFakeDns($url)->fetch();

    expect($data->get('title'))->toBe('Some video')
        ->and($timeouts)->not->toBeEmpty()
        ->and($timeouts)->each->toBe(3);
});

it('checks image size with one safe image request', function (): void {
    $image = UploadedFile::fake()->image('image.jpg', 1000, 600);
    $imageContents = file_get_contents($image->getRealPath());
    $imageUrl = 'https://images.example/image.jpg';
    $requests = [];

    Http::fake([$imageUrl => function (Request $request) use (&$requests, $imageContents): mixed {
        $requests[] = $request;

        return Http::response($imageContents, 200);
    }]);
    Http::preventStrayRequests();

    $service = metaDataWithFakeDns('https://example.com');
    $suitable = $service->checkExistsAndSize($imageUrl);

    expect($suitable)->toBeTrue()
        ->and($requests)->toHaveCount(1)
        ->and($requests[0]->method())->toBe('GET');
});

it('serves repeated previews from memo with a single store hit', function (): void {
    $url = 'https://example.com/memoized-preview';

    Event::fake([
        CacheHit::class,
        CacheMissed::class,
    ]);

    Http::fake([
        $url => Http::response('
            <html>
                <head>
                    <meta property="og:title" content="Memoized preview">
                </head>
            </html>
        ', 200),
    ]);
    Http::preventStrayRequests();

    $first = metaDataWithFakeDns($url)->fetch();
    $second = metaDataWithFakeDns($url)->fetch();
    $third = metaDataWithFakeDns($url)->fetch();

    expect($second->toArray())->toBe($first->toArray())
        ->and($third->toArray())->toBe($first->toArray())
        ->and($first->get('title'))->toBe('Memoized preview');

    Event::assertDispatched(CacheMissed::class, 1);
    Event::assertDispatched(CacheHit::class, 1);
});

it('does not reuse a preview cached under an older cache version', function (): void {
    $url = 'https://example.com/versioned-preview';
    $legacyKey = Str::of($url)->slug()->prepend('preview_')->value();

    Cache::put($legacyKey, collect(['title' => 'Stale preview']), now()->addYear());

    Http::fake([
        $url => Http::response('
            <html>
                <head>
                    <meta property="og:title" content="Fresh preview">
                </head>
            </html>
        ', 200),
    ]);
    Http::preventStrayRequests();

    $data = metaDataWithFakeDns($url)->fetch();

    expect($data->get('title'))->toBe('Fresh preview')
        ->and(Cache::get(previewCacheKey($url))?->get('title'))->toBe('Fresh preview')
        ->and(Cache::get($legacyKey)?->get('title'))->toBe('Stale preview');
});
