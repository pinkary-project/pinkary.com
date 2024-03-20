<?php

declare(strict_types=1);

use App\Services\AvatarProviders\GitHub;
use App\Services\AvatarProviders\Twitter;

test('twitter applicable', function (string $linkUrl, string $avatarUrl) {
    $provider = new Twitter();

    expect($provider->applicable($linkUrl))->toBeTrue();

    expect($provider->getUrl($linkUrl))->toBe($avatarUrl);
})->with([
    ['https://www.twitter.com/enunomaduro/', 'https://unavatar.io/twitter/enunomaduro'],
    ['https://www.twitter.com/enunomaduro', 'https://unavatar.io/twitter/enunomaduro'],
    ['https://twitter.com/enunomaduro', 'https://unavatar.io/twitter/enunomaduro'],
    ['https://www.twitter.com/enunomaduro?q=qwdwqd', 'https://unavatar.io/twitter/enunomaduro'],

    // x.com
    ['https://www.x.com/enunomaduro/', 'https://unavatar.io/twitter/enunomaduro'],
    ['https://www.x.com/enunomaduro', 'https://unavatar.io/twitter/enunomaduro'],
    ['https://x.com/enunomaduro', 'https://unavatar.io/twitter/enunomaduro'],
    ['https://www.x.com/enunomaduro?q=qwdwqd', 'https://unavatar.io/twitter/enunomaduro'],
]);

test('twitter not applicable', function (string $linkUrl) {
    $provider = new Twitter();

    expect($provider->applicable($linkUrl))->toBeFalse();
})->with([
    ['https://www.twitter.com/'],
    ['https://www.twitter.com'],
    ['https://twitter.com'],
    ['https://www.twitter.com?q=qwdwqd'],
    ['https://www.x.com/'],
    ['https://www.x.com'],
    ['https://x.com'],
    ['https://www.x.com?q=qwdwqd'],
    ['https://www.google.com/'],
    ['https://www.google.com'],
    ['https://google.com'],
    ['https://www.google.com?q=qwdwqd'],
    ['https://www.facebook.com/'],
    ['https://www.facebook.com'],
    ['https://facebook.com/qwdwqd'],
    ['https://www.facebook.com?q=qwdwqd'],
]);

test('github applicable', function (string $linkUrl, string $avatarUrl) {
    $provider = new GitHub();

    expect($provider->applicable($linkUrl))->toBeTrue();

    expect($provider->getUrl($linkUrl))->toBe($avatarUrl);
})->with([
    ['https://www.github.com/enunomaduro/', 'https://unavatar.io/github/enunomaduro'],
    ['https://www.github.com/enunomaduro', 'https://unavatar.io/github/enunomaduro'],
    ['https://github.com/enunomaduro', 'https://unavatar.io/github/enunomaduro'],
    ['https://www.github.com/enunomaduro?q=qwdwqd', 'https://unavatar.io/github/enunomaduro'],
]);

test('github not applicable', function (string $linkUrl) {
    $provider = new GitHub();

    expect($provider->applicable($linkUrl))->toBeFalse();
})->with([
    ['https://www.github.com/'],
    ['https://www.github.com'],
    ['https://github.com'],
    ['https://www.github.com?q=qwdwqd'],
    ['https://www.google.com/'],
    ['https://www.google.com'],
    ['https://google.com'],
    ['https://www.google.com?q=qwdwqd'],
    ['https://www.facebook.com/'],
    ['https://www.facebook.com'],
    ['https://facebook.com/qwdwqd'],
    ['https://www.facebook.com?q=qwdwqd'],
]);
