<?php

declare(strict_types=1);

test('link', function () {
    $content = 'Sure, here is the link: https://example.com. Let me know if you have any questions.';

    $provider = new App\Services\ParsableContent();

    Http::fake([
        'https://example.com' => Http::response('', 404),
    ]);

    expect($provider->parse($content))
        ->toMatchSnapshot();
});

test('only links with images or html oEmbeds are parsed', function () {
    $content = 'Sure, here is the link: https://laravel.com. Let me know if you have any questions.';

    $provider = new App\Services\ParsableContent();

    Http::fake([
        'https://laravel.com' => Http::response('
            <html>
                <head>
                    <meta property="og:title" content="Laravel - The PHP Framework For Web Artisans">
                    <meta property="og:type" content="website">
                    <meta property="og:url" content="https://laravel.com/">
                    <meta property="og:image" content="https://laravel.com/img/og-image.jpg">
                </head>
            </html>
        ', 200),
    ]);

    expect($provider->parse($content))
        ->toMatchSnapshot();
});

test('mention', function () {
    $content = '@nunomaduro, let me know if you have any questions. Thanks @xiCO2k.';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))
        ->toMatchSnapshot();
});

it('ignores mention inside <a>', function () {
    $content = 'https://pinkary.com/@nunomaduro';

    $provider = new App\Services\ParsableContent();

    Http::fake([
        'https://pinkary.com/@nunomaduro' => Http::response('
            <html>
                <head>
                    <meta property="og:title" content="Nuno Maduro (@nunomaduro) / Pinkary">
                    <meta property="og:type" content="profile">
                    <meta property="og:url" content="https://pinkary.com/@nunomaduro">
                    <meta property="og:image" content="https://pinkary.com/storage/avatars/120f8d175fd0146ca0541625b8bd6c742e838632951a7e58dc7fbdc8c2170c4f.png">
                </head>
            </html>
        ', 200),
    ]);

    expect($provider->parse($content))
        ->toMatchSnapshot();
});
