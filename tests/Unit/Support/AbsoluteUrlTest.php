<?php

declare(strict_types=1);

use App\Support\AbsoluteUrl;
use Illuminate\Http\Request;

beforeEach(function (): void {
    config(['app.url' => 'https://pinkary.test']);

    // Symfony's static trusted-host list survives application refreshes.
    Request::setTrustedHosts([]);
});

test('a url already on the request host is returned untouched', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::for('https://pinkary.test/storage/a.png', $request))
        ->toBe('https://pinkary.test/storage/a.png');
});

test('an app url rooted url is not rewritten for an untrusted host', function (): void {
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

test('a protocol relative url adopts the request scheme', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::for('//cdn.example.com/a.png', $request))
        ->toBe('https://cdn.example.com/a.png');
});

test('a page rooted reference resolves against the page host', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::fromPage('/storage/a.png', 'https://pinkary.test/questions/abc', $request))
        ->toBe('https://pinkary.test/storage/a.png');
});

test('a page relative reference resolves against the page directory', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::fromPage('b.png', 'https://pinkary.test/questions/abc', $request))
        ->toBe('https://pinkary.test/questions/b.png');
});

test('a page reference on another host is left untouched', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::fromPage('https://other.test/logo.png', 'https://pinkary.test/q/1', $request))
        ->toBe('https://other.test/logo.png');
});

test('a page reference that is blank or not a string is discarded', function (mixed $url): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::fromPage($url, 'https://pinkary.test/q/1', $request))->toBeNull();
})->with([null, '', '   ']);
