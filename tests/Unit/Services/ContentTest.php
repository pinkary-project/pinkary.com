<?php

declare(strict_types=1);

test('link', function () {
    $content = 'Sure, here is the link: example.com. Let me know if you have any questions.';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('Sure, here is the link: <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>. Let me know if you have any questions.');
});
