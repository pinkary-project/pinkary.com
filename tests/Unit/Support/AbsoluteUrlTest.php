<?php

declare(strict_types=1);

use App\Support\AbsoluteUrl;
use Illuminate\Http\Request;

beforeEach(function (): void {
    config(['app.url' => 'https://pinkary.test']);

    // Symfony's trusted-host list is static, so it survives whatever ran
    // earlier in this parallel worker and rejects the hosts used here.
    Request::setTrustedHosts([]);
});

test('a url already on the request host is returned untouched', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::for('https://pinkary.test/storage/a.png', $request))
        ->toBe('https://pinkary.test/storage/a.png');
});

test('an app url rooted url is not rewritten for an untrusted host', function (): void {
    // The request claims to be another origin. Without the allowlist the
    // app-rooted URL is kept, so every avatar and image in the response
    // still points at APP_URL rather than at the caller's chosen host.
    $request = Request::create('https://evil.test/api/v1/feed');

    expect(AbsoluteUrl::for('https://pinkary.test/storage/a.png', $request))
        ->toBe('https://pinkary.test/storage/a.png');
});

test('a localhost url is always rewritten, for local development', function (): void {
    $request = Request::create('http://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::for('http://localhost/storage/a.png', $request))
        ->toBe('http://pinkary.test/storage/a.png');
});

test('a relative path is resolved against APP_URL, not the request host', function (): void {
    $request = Request::create('https://evil.test/api/v1/feed');

    expect(AbsoluteUrl::for('storage/a.png', $request))
        ->toBe('https://pinkary.test/storage/a.png');
});

test('blank and non string values become null', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::for(null, $request))->toBeNull()
        ->and(AbsoluteUrl::for('   ', $request))->toBeNull()
        ->and(AbsoluteUrl::for(42, $request))->toBeNull();
});

test('an external url is never rewritten', function (): void {
    $request = Request::create('http://phone.local/api/v1/feed');

    expect(AbsoluteUrl::for('https://example.com/logo.png', $request))
        ->toBe('https://example.com/logo.png');
});
