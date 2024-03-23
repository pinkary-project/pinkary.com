<?php

declare(strict_types=1);

test('brs', function (string $content, string $parsed) {
    $provider = new App\Services\ParsableContentProviders\BrProviderParsable();

    expect($provider->parse($content))->toBe($parsed);
})->with([
    [
        'content' => "Check this:\nHello, World!",
        'parsed' => 'Check this:<br>Hello, World!',
    ],
    [
        'content' => "Check this:\n\nHello, World!\n",
        'parsed' => 'Check this:<br><br>Hello, World!<br>',
    ],
]);

test('links', function (string $content, string $parsed) {
    $provider = new App\Services\ParsableContentProviders\LinkProviderParsable();

    expect($provider->parse($content))->toBe($parsed);
})->with([
    [
        'content' => 'https://example.com/',
        'parsed' => '<a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com/">example.com</a>',
    ],
    [
        'content' => 'Hello https://example.com',
        'parsed' => 'Hello <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>',
    ],
    [
        'content' => 'Hello https://example.com, how are you?',
        'parsed' => 'Hello <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>, how are you?',
    ],
    [
        'content' => 'Hello https://example.com, how are you? https://example.com',
        'parsed' => 'Hello <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>, how are you? <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>',
    ],
]);

test('links with mail', function (string $content, string $parsed) {
    $provider = new App\Services\ParsableContentProviders\LinkProviderParsable();

    expect($provider->parse($content))->toBe($parsed);
})->with([
    [
        'content' => 'javier@example.com',
        'parsed' => '<a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:javier@example.com">javier@example.com</a>',
    ],
    [
        'content' => 'Hello my email is javier@example.com',
        'parsed' => 'Hello my email is <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:javier@example.com">javier@example.com</a>',
    ],
    [
        'content' => 'Hello my email is javier@example.com, and my site is https://example.com',
        'parsed' => 'Hello my email is <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:javier@example.com">javier@example.com</a>, and my site is <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>',
    ],
    [
        'content' => 'Hello my emails are javier@example.com, contact@example.com and support@example.com.',
        'parsed' => 'Hello my emails are <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:javier@example.com">javier@example.com</a>, <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:contact@example.com">contact@example.com</a> and <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:support@example.com">support@example.com</a>.',
    ],
]);

test('code', function (string $content) {
    $provider = new App\Services\ParsableContentProviders\CodeProviderParsable();

    expect($provider->parse($content))->toMatchSnapshot();
})->with([
    [
        'content' => <<<'EOL'
            ```php
            echo "Hello, World!";
            ```
            EOL,
    ],
    [
        'content' => <<<'EOL'
            Check this:
            ```
            echo "Hello, World!";
            ```

            and this:
            ```php
            echo "Hello, World!";
            ```
            EOL,
    ],
]);
