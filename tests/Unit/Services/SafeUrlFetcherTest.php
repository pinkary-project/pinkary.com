<?php

declare(strict_types=1);

use App\Services\BoundedResponseBody;
use App\Services\SafeUrlFetcher;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;

/**
 * @param  list<PsrResponse|Closure(RequestInterface, array<array-key, mixed>): PsrResponse>  $responses
 */
function mockSafeUrlFetcherResponses(array $responses): MockHandler
{
    $mockHandler = new MockHandler($responses);

    Http::globalMiddleware(static fn (callable $handler): Closure => static fn (RequestInterface $request, array $options): PromiseInterface => $mockHandler($request, $options));

    return $mockHandler;
}

it('rejects private and reserved IP addresses without sending a request', function (string $url): void {
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse]);

    $response = (new SafeUrlFetcher)->get($url);

    expect($response)->toBeNull()
        ->and(count($mockHandler))->toBe(1);
})->with([
    'private IPv4' => 'http://10.0.0.1/metadata',
    'loopback IPv4' => 'http://127.0.0.1/admin',
    'link-local IPv4' => 'http://169.254.169.254/latest/meta-data/',
    'private IPv6' => 'http://[fc00::1]/',
    'loopback IPv6' => 'http://[::1]/',
    'reserved IPv6' => 'http://[2001:db8::1]/',
]);

it('rejects localhost names without sending a request', function (string $url): void {
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse]);

    $response = (new SafeUrlFetcher)->get($url);

    expect($response)->toBeNull()
        ->and(count($mockHandler))->toBe(1);
})->with([
    'localhost' => 'http://localhost/admin',
    'localhost subdomain' => 'http://api.localhost/admin',
]);

it('rejects malformed URLs without sending a request', function (string $url): void {
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse]);

    expect((new SafeUrlFetcher)->get($url))->toBeNull()
        ->and(count($mockHandler))->toBe(1);
})->with([
    'contains whitespace' => 'https://public.example/a path',
    'cannot be parsed' => 'http://',
    'contains an encoded host' => 'https://public%2eexample/',
    'uses an invalid port' => 'https://public.example:0/',
]);

it('rejects a DNS answer containing any private address', function (): void {
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse]);

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => [
        '93.184.216.34',
        '192.168.1.10',
    ]);

    expect($fetcher->get('https://rebound.example/'))->toBeNull()
        ->and(count($mockHandler))->toBe(1);
});

it('rejects empty or non-list DNS answers', function (array $addresses): void {
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse]);
    $fetcher = new SafeUrlFetcher(static fn (string $host): array => $addresses);

    expect($fetcher->get('https://public.example/'))->toBeNull()
        ->and(count($mockHandler))->toBe(1);
})->with([
    'empty' => [[]],
    'associative' => [['address' => '93.184.216.34']],
]);

it('rejects a DNS lookup that throws', function (): void {
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse]);
    $fetcher = new SafeUrlFetcher(static fn (string $host): array => throw new RuntimeException('DNS lookup failed.'));

    expect($fetcher->get('https://public.example/'))->toBeNull()
        ->and(count($mockHandler))->toBe(1);
});

it('rejects a NAT64 address that embeds a non-global IPv4 destination', function (): void {
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse]);
    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['64:ff9b::a9fe:a9fe']);

    expect($fetcher->get('https://metadata.example/'))->toBeNull()
        ->and(count($mockHandler))->toBe(1);
});

it('pins every validated DNS answer and disables proxies', function (): void {
    $url = 'https://public.example/page';
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse(200, [], 'page')]);

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => [
        '93.184.216.34',
        '2606:4700:4700::1111',
    ]);

    $response = $fetcher->get($url);
    $requestOptions = $mockHandler->getLastOptions();

    expect($response?->ok())->toBeTrue()
        ->and($requestOptions['curl'][CURLOPT_RESOLVE])->toBe(['public.example:443:93.184.216.34,[2606:4700:4700::1111]'])
        ->and($requestOptions['proxy'])->toBe('')
        ->and($requestOptions['allow_redirects'])->toBeFalse()
        ->and($mockHandler)->toBeEmpty();
});

it('rejects a response with a Content-Length larger than the body limit', function (): void {
    $url = 'https://public.example/large-page';
    $mockHandler = mockSafeUrlFetcherResponses([
        new PsrResponse(200, [
            'Content-Length' => (string) (SafeUrlFetcher::MAX_RESPONSE_BYTES + 1),
        ], 'page'),
    ]);

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);
    $response = $fetcher->get($url);
    $responseBody = $mockHandler->getLastOptions()['sink'];

    expect($response)->toBeNull()
        ->and($responseBody)->toBeInstanceOf(BoundedResponseBody::class)
        ->and($responseBody->getSize())->toBe(0)
        ->and($mockHandler)->toBeEmpty();
});

it('aborts before reading a response whose Content-Length exceeds the limit', function (): void {
    $url = 'https://public.example/large-header';
    $responseBody = null;

    $mockHandler = mockSafeUrlFetcherResponses([static function (RequestInterface $request, array $options) use (&$responseBody): PsrResponse {
        $responseBody = $options['sink'];

        expect(fn () => $options['on_headers'](new PsrResponse(200, [
            'Content-Length' => (string) (SafeUrlFetcher::MAX_RESPONSE_BYTES + 1),
        ])))->toThrow(RuntimeException::class);

        return new PsrResponse;
    }]);

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    expect($fetcher->get($url))->toBeNull()
        ->and($responseBody)->toBeInstanceOf(BoundedResponseBody::class)
        ->and($responseBody->getSize())->toBe(0)
        ->and($mockHandler)->toBeEmpty();
});

it('caps a chunked response body at the byte limit', function (): void {
    $url = 'https://public.example/chunked-page';
    $responseBody = null;

    $mockHandler = mockSafeUrlFetcherResponses([static function (RequestInterface $request, array $options) use (&$responseBody): PsrResponse {
        $responseBody = $options['sink'];
        $responseBody->write(str_repeat('é', intdiv(SafeUrlFetcher::MAX_RESPONSE_BYTES, 2)));
        $responseBody->write('x');

        return new PsrResponse(200, ['Transfer-Encoding' => 'chunked']);
    }]);

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    expect($fetcher->get($url))->toBeNull()
        ->and($responseBody)->toBeInstanceOf(BoundedResponseBody::class)
        ->and($responseBody->getSize())->toBe(SafeUrlFetcher::MAX_RESPONSE_BYTES)
        ->and($mockHandler)->toBeEmpty();
});

it('blocks redirects to private IP addresses before sending a second request', function (): void {
    $url = 'https://public.example/start';
    $mockHandler = mockSafeUrlFetcherResponses([
        new PsrResponse(302, ['Location' => 'http://127.0.0.1/admin']),
        new PsrResponse,
    ]);

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    expect($fetcher->get($url))->toBeNull()
        ->and((string) $mockHandler->getLastRequest()?->getUri())->toBe($url)
        ->and(count($mockHandler))->toBe(1);
});

it('rejects a DNS rebinding introduced before a redirect', function (): void {
    $url = 'https://public.example/start';
    $lookups = 0;

    $mockHandler = mockSafeUrlFetcherResponses([
        new PsrResponse(302, ['Location' => 'https://public.example/internal']),
        new PsrResponse,
    ]);

    $fetcher = new SafeUrlFetcher(static function (string $host) use (&$lookups): array {
        $lookups++;

        return $lookups === 1 ? ['93.184.216.34'] : ['10.0.0.2'];
    });

    expect($fetcher->get($url))->toBeNull()
        ->and((string) $mockHandler->getLastRequest()?->getUri())->toBe($url)
        ->and(count($mockHandler))->toBe(1)
        ->and($lookups)->toBe(2);
});

it('returns a redirect response without a location header', function (): void {
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse(302)]);
    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    $response = $fetcher->get('https://public.example/start');

    expect($response?->status())->toBe(302)
        ->and($mockHandler)->toBeEmpty();
});

it('stops after the maximum number of redirects', function (): void {
    $mockHandler = mockSafeUrlFetcherResponses(array_fill(0, 6, new PsrResponse(302, ['Location' => '/next'])));
    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    expect($fetcher->get('https://public.example/start'))->toBeNull()
        ->and($mockHandler)->toBeEmpty();
});

it('rejects a malformed redirect location', function (): void {
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse(302, ['Location' => 'http://[::1'])]);
    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    expect($fetcher->get('https://public.example/start'))->toBeNull()
        ->and($mockHandler)->toBeEmpty();
});

it('rethrows request exceptions that are not caused by an oversized response', function (): void {
    mockSafeUrlFetcherResponses([new RuntimeException('The request failed.')]);
    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);

    expect(fn (): ?\Illuminate\Http\Client\Response => $fetcher->get('https://public.example/'))->toThrow(RuntimeException::class, 'The request failed.');
});

it('fetches a valid public URL using only its validated DNS answer', function (): void {
    $url = 'https://public.example/page';

    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse(200, [], 'page')]);

    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['93.184.216.34']);
    $response = $fetcher->get($url);

    expect($response?->ok())->toBeTrue()
        ->and($response?->body())->toBe('page')
        ->and($mockHandler)->toBeEmpty();
});

it('allows a NAT64 address that embeds a public IPv4 destination', function (): void {
    $mockHandler = mockSafeUrlFetcherResponses([new PsrResponse(200, [], 'page')]);
    $fetcher = new SafeUrlFetcher(static fn (string $host): array => ['64:ff9b::5db8:d822']);

    $response = $fetcher->get('https://public.example/page');

    expect($response?->ok())->toBeTrue()
        ->and($response?->body())->toBe('page')
        ->and($mockHandler)->toBeEmpty();
});
