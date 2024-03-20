<?php

declare(strict_types=1);

use App\Services\Avatar;

test('avatar url without links', function () {
    $avatar = new Avatar(
        email: 'enunomaduro@gmail.com',
        links: [],
    );

    expect($avatar->url())->toBe('https://unavatar.io/enunomaduro@gmail.com?fallback=https://gravatar.com/avatar/86cfef5c1f5195df1a9db17a5f8ecb34455e1f0133a725de9acf7f2fb26ac6a1?s=300');
});

test('avatar url', function () {
    $avatar = new Avatar(
        email: 'enunomaduro@gmail.com',
        links: [
            'https://twitter.com/@enunomaduro',
        ],
    );

    expect($avatar->url())->toBe('https://unavatar.io/twitter/enunomaduro?fallback=https://unavatar.io/enunomaduro@gmail.com?fallback=https://gravatar.com/avatar/86cfef5c1f5195df1a9db17a5f8ecb34455e1f0133a725de9acf7f2fb26ac6a1?s=300');
});

test('avatar url with multiple links', function () {
    $avatar = new Avatar(
        email: 'taylor@laravel.com',
        links: [
            'https://twitter.com/taylorotwell',
            'https://x.com/@laravelphp',
            'https://github.com/taylorotwell',
        ],
    );

    expect($avatar->url())->toBe('https://unavatar.io/twitter/laravelphp?fallback=https://unavatar.io/taylor@laravel.com?fallback=https://unavatar.io/twitter/taylorotwell?fallback=https://unavatar.io/github/taylorotwell?fallback=https://gravatar.com/avatar/75cc0771635c9940dd8bf7f884ee259b7502eb4a5797b5265d830e530b05ae7d?s=300');
});

test('avatar url with multiple links with _', function () {
    $avatar = new Avatar(
        email: 'taylor@laravel.com',
        links: [
            'https://twitter.com/taylorotwell_',
            'https://x.com/@laravelphp_',
            'https://github.com/taylorotwell_',
        ],
    );

    expect($avatar->url())->toBe('https://unavatar.io/twitter/laravelphp_?fallback=https://unavatar.io/taylor@laravel.com?fallback=https://unavatar.io/twitter/taylorotwell_?fallback=https://unavatar.io/github/taylorotwell_?fallback=https://gravatar.com/avatar/75cc0771635c9940dd8bf7f884ee259b7502eb4a5797b5265d830e530b05ae7d?s=300');
});
