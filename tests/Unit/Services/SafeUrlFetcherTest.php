<?php

declare(strict_types=1);

use App\Services\BoundedResponseBody;
use App\Services\SafeUrlFetcher;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

it('rejects private and reserved IP addresses without sending a request', function (string $url): void {
    Http::preventStrayRequests();

    $response = (new SafeUrlFetcher)->get($url);

    expect($response)->toBeNull();
    Http::assertNothingSent();
})->with([
    'private IPv4' => 'http://10.0.0.1/metadata',
    'loopback IPv4' => 'http://127.0.0.1/admin',
    'link-local IPv4' => 'http://169.254.169.254/latest/meta-data/',
    'private IPv6' => 'http://[fc00::1]/',
    'loopback IPv6' => 'http://[::1]/',
    'reserved IPv6' => 'http://[2001:db8::1]/',
]);

it('rejects localhost names without sending a request', function (string $url): void {
    Http::preventStrayRequests();

    $response = (new SafeUrlFetcher)->get($url);

    expect($response)->toBeNull();
    Http::assertNothingSent();
})->with([
    'localhost' => 'http://localhost/admin',
    'localhost subdomain' => 'http://api.localhost/admin',
]);

it('rejects a DNS answer containing any private address', function (): void {
    Http::preventStrayRequests();

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => [
        '93.184.216.34',
        '192.168.1.10',
    ]);

    expect($fetcher->get('https://rebound.example/'))->toBeNull();
    Http::assertNothingSent();
});

it('pins every validated DNS answer and disables proxies', function (): void {
    $url = 'https://public.example/page';
    $requestOptions = [];

    Http::fake([$url => function (Request $request, array $options) use (&$requestOptions): mixed {
        $requestOptions = $options;

        return Http::response('page', 200);
    }]);
    Http::preventStrayRequests();

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => [
        '93.184.216.34',
        '2606:4700:4700::1111',
    ]);

    $response = $fetcher->get($url);

    expect($response?->ok())->toBeTrue()
        ->and($requestOptions['curl'][CURLOPT_RESOLVE])->toBe(['public.example:443:93.184.216.34,[2606:4700:4700::1111]'])
        ->and($requestOptions['proxy'])->toBe('')
        ->and($requestOptions['allow_redirects'])->toBeFalse();
});

it('rejects a response with a Content-Length larger than the body limit', function (): void {
    $url = 'https://public.example/large-page';
    $responseBody = null;

    Http::fake([$url => function (Request $request, array $options) use (&$responseBody): mixed {
        $responseBody = $options['sink'];

        return Http::response('page', 200, [
            'Content-Length' => (string) (SafeUrlFetcher::MAX_RESPONSE_BYTES + 1),
        ]);
    }]);
    Http::preventStrayRequests();

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    expect($fetcher->get($url))->toBeNull()
        ->and($responseBody)->toBeInstanceOf(BoundedResponseBody::class)
        ->and($responseBody->getSize())->toBe(0);
});

it('aborts before reading a response whose Content-Length exceeds the limit', function (): void {
    $url = 'https://public.example/large-header';
    $responseBody = null;

    Http::fake([$url => function (Request $request, array $options) use (&$responseBody): mixed {
        $responseBody = $options['sink'];

        expect(fn () => $options['on_headers'](new PsrResponse(200, [
            'Content-Length' => (string) (SafeUrlFetcher::MAX_RESPONSE_BYTES + 1),
        ])))->toThrow(RuntimeException::class);

        return Http::response('page');
    }]);
    Http::preventStrayRequests();

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    expect($fetcher->get($url))->toBeNull()
        ->and($responseBody)->toBeInstanceOf(BoundedResponseBody::class)
        ->and($responseBody->getSize())->toBe(0);
});

it('caps a chunked response body at the byte limit', function (): void {
    $url = 'https://public.example/chunked-page';
    $responseBody = null;

    Http::fake([$url => function (Request $request, array $options) use (&$responseBody): mixed {
        $responseBody = $options['sink'];
        $responseBody->write(str_repeat('é', intdiv(SafeUrlFetcher::MAX_RESPONSE_BYTES, 2)));

        expect(fn () => $responseBody->write('x'))->toThrow(RuntimeException::class);

        return Http::response('', 200, ['Transfer-Encoding' => 'chunked']);
    }]);
    Http::preventStrayRequests();

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    expect($fetcher->get($url))->toBeNull()
        ->and($responseBody)->toBeInstanceOf(BoundedResponseBody::class)
        ->and($responseBody->getSize())->toBe(SafeUrlFetcher::MAX_RESPONSE_BYTES);
});

it('blocks redirects to private IP addresses before sending a second request', function (): void {
    $url = 'https://public.example/start';
    $requests = [];

    Http::fake([$url => function (Request $request) use (&$requests): mixed {
        $requests[] = $request->url();

        return Http::response('', 302, ['Location' => 'http://127.0.0.1/admin']);
    }]);
    Http::preventStrayRequests();

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    expect($fetcher->get($url))->toBeNull()
        ->and($requests)->toBe([$url]);
});

it('rejects a DNS rebinding introduced before a redirect', function (): void {
    $url = 'https://public.example/start';
    $requests = [];
    $lookups = 0;

    Http::fake([$url => function (Request $request) use (&$requests): mixed {
        $requests[] = $request->url();

        return Http::response('', 302, ['Location' => 'https://public.example/internal']);
    }]);
    Http::preventStrayRequests();

    $fetcher = new SafeUrlFetcher(static function (string $host) use (&$lookups): array {
        $lookups++;

        return $lookups === 1 ? ['93.184.216.34'] : ['10.0.0.2'];
    });

    expect($fetcher->get($url))->toBeNull()
        ->and($requests)->toBe([$url])
        ->and($lookups)->toBe(2);
});

it('fetches a valid public URL using only its validated DNS answer', function (): void {
    $url = 'https://public.example/page';

    Http::fake([$url => Http::response('page', 200)]);
    Http::preventStrayRequests();

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);
    $response = $fetcher->get($url);

    expect($response?->ok())->toBeTrue()
        ->and($response?->body())->toBe('page');
});
