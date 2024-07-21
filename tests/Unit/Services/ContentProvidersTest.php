<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;

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
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com/">example.com</a>',
    ],
    [
        'content' => 'https://example.media/',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.media/">example.media</a>',
    ],
    [
        'content' => 'https://example.co.uk/',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.co.uk/">example.co.uk</a>',
    ],
    [
        'content' => 'Hello https://example.com',
        'parsed' => 'Hello <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>',
    ],
    [
        'content' => 'Hello https://example.com, how are you?',
        'parsed' => 'Hello <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>, how are you?',
    ],
    [
        'content' => 'Hello https://example.com, how are you? https://example.com',
        'parsed' => 'Hello <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>, how are you? <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>',
    ],
    [
        'content' => 'You can check in this link: https://example.com. Or you can check in this other link: https://example.media.',
        'parsed' => 'You can check in this link: <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>. Or you can check in this other link: <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.media">example.media</a>.',
    ],
]);

test('links with mail', function (string $content, string $parsed) {
    $provider = new App\Services\ParsableContentProviders\LinkProviderParsable();

    expect($provider->parse($content))->toBe($parsed);
})->with([
    [
        'content' => 'javier@example.com',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:javier@example.com">javier@example.com</a>',
    ],
    [
        'content' => 'Hello my email is javier@example.com',
        'parsed' => 'Hello my email is <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:javier@example.com">javier@example.com</a>',
    ],
    [
        'content' => 'Hello my email is javier@example.com, and my site is https://example.com',
        'parsed' => 'Hello my email is <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:javier@example.com">javier@example.com</a>, and my site is <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>',
    ],
    [
        'content' => 'Hello my emails are javier@example.com, contact@example.com and support@example.com.',
        'parsed' => 'Hello my emails are <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:javier@example.com">javier@example.com</a>, <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:contact@example.com">contact@example.com</a> and <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="mailto:support@example.com">support@example.com</a>.',
    ],
]);

test('link parser doesnt ignores the src attribute in am image tag, only the alt attribute', function (string $content, string $parsed) {
    $provider = new App\Services\ParsableContentProviders\LinkProviderParsable();

    expect($provider->parse($content))->toBe($parsed);
})->with([
    [
        'content' => '<img src="/storage/images/pathtoimage.jpg" alt="Example">',
        'parsed' => '<img src="/storage/images/pathtoimage.jpg" alt="Example">',
    ],
])->note('The dot in the path was causing a anchor tag to be parsed so we added the src attribute to the REGEX');

test('links with ports in the url', function (string $content, string $parsed) {
    $provider = new App\Services\ParsableContentProviders\LinkProviderParsable();

    expect($provider->parse($content))->toBe($parsed);
})->with([
    [
        'content' => 'https://example.com:8080',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com:8080">example.com:8080</a>',
    ],
    [
        'content' => 'https://example.com:8080/',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com:8080/">example.com:8080</a>',
    ],
    [
        'content' => 'https://example.com:8080/?utm_source=twitter',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com:8080/?utm_source=twitter">example.com:8080/?utm_source=twitter</a>',
    ],
    [
        'content' => 'https://example.com:8080/?utm_source=twitter&utm_medium=social',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com:8080/?utm_source=twitter&utm_medium=social">example.com:8080/?utm_source=twitter&utm_medium=social</a>',
    ],
    [
        'content' => 'https://example.com:8080/?utm_source=twitter&utm_medium=social&utm_campaign=example',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com:8080/?utm_source=twitter&utm_medium=social&utm_campaign=example">example.com:8080/?utm_source=twitter&utm_medium=social&utm_campaign=example</a>',
    ],
    [
        'content' => 'https://example.com:8080/@nunomaduro',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com:8080/@nunomaduro">example.com:8080/@nunomaduro</a>',
    ],
]);

test('links with localhost or ip addresses', function (string $content, string $parsed) {
    $provider = new App\Services\ParsableContentProviders\LinkProviderParsable();

    expect($provider->parse($content))->toBe($parsed);
})->with([
    [
        'content' => 'http://localhost',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="http://localhost">localhost</a>',
    ],
    [
        'content' => 'http://localhost/@nunomaduro',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="http://localhost/@nunomaduro">localhost/@nunomaduro</a>',
    ],
    [
        'content' => 'http://127.0.0.1',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="http://127.0.0.1">127.0.0.1</a>',
    ],
    [
        'content' => 'http://127.0.0.1/@nunomaduro',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="http://127.0.0.1/@nunomaduro">127.0.0.1/@nunomaduro</a>',
    ],
]);

test('links with query params', function (string $content, string $parsed) {
    $provider = new App\Services\ParsableContentProviders\LinkProviderParsable();

    expect($provider->parse($content))->toBe($parsed);
})->with([
    [
        'content' => 'https://example.com/?utm_source=twitter',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com/?utm_source=twitter">example.com/?utm_source=twitter</a>',
    ],
    [
        'content' => 'https://example.com/?utm_source=twitter&utm_medium=social',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com/?utm_source=twitter&utm_medium=social">example.com/?utm_source=twitter&utm_medium=social</a>',
    ],
    [
        'content' => 'https://example.com/?utm_source=twitter&utm_medium=social&utm_campaign=example',
        'parsed' => '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com/?utm_source=twitter&utm_medium=social&utm_campaign=example">example.com/?utm_source=twitter&utm_medium=social&utm_campaign=example</a>',
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
    ['content' => '@w31r4_'],
    ['content' => '@nunomaduro.'],
    ['content' => '@nunomaduro,'],
    ['content' => '@nunomaduro!'],
    ['content' => '@nunomaduro?'],
    ['content' => '@nunomaduro/'],
]);

test('image exists', function () {
    Storage::fake('public');
    $provider = new App\Services\ParsableContentProviders\ImageProviderParsable();

    $pngFile = UploadedFile::fake()->image('pathtoimage.png');
    $filePath = 'images/pathtoimage.png';
    Storage::disk('public')->put($filePath, $pngFile->getContent());

    // Use the direct file path in the markdown
    $content = "![]({$filePath})";

    expect($provider->parse($content))
        ->toMatchSnapshot();
});

test('image does not exists', function () {
    $provider = new App\Services\ParsableContentProviders\ImageProviderParsable();

    $content = '![](images/imagesdoesnotexists.png)';

    expect($provider->parse($content))->toBe('...');

    $content = 'other content ![](images/imagesdoesnotexists.png)';

    expect($provider->parse($content))->toBe('other content ');
});
