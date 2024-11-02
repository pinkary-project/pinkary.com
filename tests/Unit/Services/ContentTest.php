<?php

declare(strict_types=1);

test('link', function () {
    $content = 'Sure, here is the link: https://example.com. Let me know if you have any questions.';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))
        ->toMatchSnapshot();
});

test('only links with images or html oEmbeds are parsed', function () {
    $content = 'Sure, here is the link: https://laravel.com. Let me know if you have any questions.';

    $provider = new App\Services\ParsableContent();

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

    expect($provider->parse($content))
        ->toMatchSnapshot();

});
