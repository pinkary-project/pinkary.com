<?php

declare(strict_types=1);

use App\Support\AbsoluteUrl;
use Illuminate\Http\Request;

beforeEach(function (): void {
    config(['app.url' => 'https://pinkary.test']);
    config(['trusted-hosts.hosts' => []]);

    // Symfony's trusted-host list is static, so it survives from whatever ran
    // earlier in this parallel worker and rejects the hosts these tests need.
    // AbsoluteUrl has its own allowlist, which is what is under test here.
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

test('a host named in the allowlist is rewritten onto the request root', function (): void {
    config(['trusted-hosts.hosts' => ['phone.local']]);

    $request = Request::create('http://phone.local:8080/api/v1/feed');

    expect(AbsoluteUrl::for('https://pinkary.test/storage/a.png', $request))
        ->toBe('http://phone.local:8080/storage/a.png');
});

test('a localhost url is always rewritten, for local development', function (): void {
    $request = Request::create('http://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::for('http://localhost/storage/a.png', $request))
        ->toBe('http://pinkary.test/storage/a.png');
});

test('a relative path is resolved against a trusted request root only', function (): void {
    $untrusted = Request::create('https://evil.test/api/v1/feed');

    expect(AbsoluteUrl::for('storage/a.png', $untrusted))
        ->toBe('https://pinkary.test/storage/a.png');

    config(['trusted-hosts.hosts' => ['phone.local']]);
    $trusted = Request::create('http://phone.local/api/v1/feed');

    expect(AbsoluteUrl::for('storage/a.png', $trusted))
        ->toBe('http://phone.local/storage/a.png');
});

test('blank and non string values become null', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::for(null, $request))->toBeNull()
        ->and(AbsoluteUrl::for('   ', $request))->toBeNull()
        ->and(AbsoluteUrl::for(42, $request))->toBeNull();
});

test('an external url is never rewritten', function (): void {
    config(['trusted-hosts.hosts' => ['phone.local']]);

    $request = Request::create('http://phone.local/api/v1/feed');

    expect(AbsoluteUrl::for('https://example.com/logo.png', $request))
        ->toBe('https://example.com/logo.png');
});

test('a page embedded app url resolves through the same allowlist', function (): void {
    $page = 'https://pinkary.test/questions/abc';

    $trusted = Request::create('http://phone.local/api/v1/questions/abc');
    config(['trusted-hosts.hosts' => ['phone.local']]);

    expect(AbsoluteUrl::fromPage('/storage/a.png', $page, $trusted))
        ->toBe('http://phone.local/storage/a.png');
});
