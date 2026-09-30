<?php

declare(strict_types=1);

use App\Actions\Auth\CreateToken;
use App\Models\User;
use App\Support\AbsoluteUrl;
use App\Support\ImagePath;
use Illuminate\Http\Request;

beforeEach(function (): void {
    config(['app.url' => 'https://pinkary.test']);
    config(['trusted-hosts.hosts' => []]);
});

// AbsoluteUrl -----------------------------------------------------------------

test('a protocol relative url adopts the request scheme', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::for('//cdn.example.com/a.png', $request))
        ->toBe('https://cdn.example.com/a.png');
});

test('a page url that is blank or not a string is discarded', function (mixed $url): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::fromPage($url, 'https://pinkary.test/q/1', $request))->toBeNull();
})->with([
    'null' => [null],
    'blank' => ['   '],
]);

test('a page protocol relative reference adopts the page scheme', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    // The preview card's image is stored protocol relative by the parser.
    expect(AbsoluteUrl::fromPage('//cdn.example.com/a.png', 'https://pinkary.test/q/1', $request))
        ->toBe('https://cdn.example.com/a.png');
});

test('a page rooted reference is resolved against the page host', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::fromPage('/images/a.png', 'https://pinkary.test/q/1', $request))
        ->toBe('https://pinkary.test/images/a.png');
});

test('a page relative reference is resolved against the page directory', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    expect(AbsoluteUrl::fromPage('b.png', 'https://pinkary.test/questions/abc', $request))
        ->toBe('https://pinkary.test/questions/b.png');
});

test('a page reference on another host is returned untouched', function (): void {
    $request = Request::create('https://pinkary.test/api/v1/feed');

    // Not one of our uploads, so it must not be rebuilt onto the API host.
    expect(AbsoluteUrl::fromPage('https://other.test/logo.png', 'https://pinkary.test/q/1', $request))
        ->toBe('https://other.test/logo.png');
});

test('a page reference served from the app host is normalized like any other', function (): void {
    $request = Request::create('https://phone.local/api/v1/feed');
    config(['trusted-hosts.hosts' => ['phone.local']]);

    expect(AbsoluteUrl::fromPage(
        'https://pinkary.test/images/a.png',
        'https://pinkary.test/questions/abc',
        $request,
    ))->toBe('https://phone.local/images/a.png');
});

test('a stored url is left alone when the application has no url of its own', function (): void {
    // With APP_URL unset there is no host to compare a stored absolute URL
    // against, so rewriting it onto the request would be a guess. Leaving
    // the stored value is the safer of the two.
    config(['app.url' => null]);
    config(['trusted-hosts.hosts' => ['phone.local']]);

    $request = Request::create('https://phone.local/api/v1/feed');

    expect(AbsoluteUrl::for('https://pinkary.test/images/a.png', $request))
        ->toBe('https://pinkary.test/images/a.png');
});

// ImagePath -------------------------------------------------------------------

test('a malformed reference degrades to itself instead of an empty string', function (): void {
    // parse_url() returns false for an invalid port, so there is no path to
    // work with and the reference is used as-is.
    expect(ImagePath::toRelative('http://example.com:notaport/a.png'))
        ->toBe('http://example.com:notaport/a.png');
});

// CreateToken -----------------------------------------------------------------

test('a token gets no expiry when the config has no expiration', function (): void {
    config(['sanctum.expiration' => null]);

    $user = User::factory()->create();

    $token = (new CreateToken)->handle($user);

    expect($token)->not->toBeEmpty()
        ->and($user->tokens()->first()->expires_at)->toBeNull();
});

test('a token honours a configured expiry', function (): void {
    config(['sanctum.expiration' => 60]);

    $user = User::factory()->create();

    (new CreateToken)->handle($user);

    expect($user->tokens()->first()->expires_at)->not->toBeNull();
});
