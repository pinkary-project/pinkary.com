<?php

declare(strict_types=1);

test('link', function () {
    $content = 'Sure, here is the link: https://example.com. Let me know if you have any questions.';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('Sure, here is the link: <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>. Let me know if you have any questions.');
});

test('mention', function () {
    $content = '@nunomaduro, let me know if you have any questions. Thanks @xiCO2k.';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('<a href="/@nunomaduro" data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>@nunomaduro</a>, let me know if you have any questions. Thanks <a href="/@xiCO2k" data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>@xiCO2k</a>.');
});

it('ignores mention inside <a>', function () {
    $content = 'https://pinkary.com/@nunomaduro';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://pinkary.com/@nunomaduro">pinkary.com/@nunomaduro</a>');
});
