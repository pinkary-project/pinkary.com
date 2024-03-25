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
        'content' => 'https://example.media/',
        'parsed' => '<a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.media/">example.media</a>',
    ],
    [
        'content' => 'https://example.co.uk/',
        'parsed' => '<a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.co.uk/">example.co.uk</a>',
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
    [
        'content' => 'You can check in this link: https://example.com. Or you can check in this other link: https://example.media.',
        'parsed' => 'You can check in this link: <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>. Or you can check in this other link: <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.media">example.media</a>.',
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

test('mention', function (string $content) {
    $provider = new App\Services\ParsableContentProviders\MentionProviderParsable();

    expect($provider->parse($content))->toMatchSnapshot();
})->with([
    ['content' => 'Hi @nunomaduro'],
    ['content' => '@nunomaduro hi'],
    ['content' => '@w31r4_-NAME'],
    ['content' => '@nunomaduro.'],
    ['content' => '@nunomaduro,'],
    ['content' => '@nunomaduro!'],
    ['content' => '@nunomaduro?'],
    ['content' => '@nunomaduro/'],
]);

test('paragraph', function (string $content, string $parsed) {
    $provider = new App\Services\ParsableContentProviders\ParagraphProviderParsable();

    expect($provider->parse($content))->toBe($parsed);
})->with([
    [
        'content' => 'Hello, World!',
        'parsed' => '<p>Hello, World!</p>',
    ],
    [
        'content' => "Hello, World!\n\nCheck this!",
        'parsed' => '<p>Hello, World!</p><p>Check this!</p>',
    ],
]);
