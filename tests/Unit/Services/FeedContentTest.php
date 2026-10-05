<?php

declare(strict_types=1);

use App\Services\FeedContent;

it('keeps links code unicode and line breaks while moving images in their original order', function (): void {
    $content = (new FeedContent)->parse('Hello 👋<br>before<img src="/one.png" alt="One">between<img src="/two.png">after<br><a href="https://example.com">example</a><pre><code>&lt;img src="x"&gt;</code></pre>');

    expect($content)->toBe([
        'html' => 'Hello 👋<br>beforebetweenafter<br><a href="https://example.com">example</a><pre><code>&lt;img src="x"&gt;</code></pre>',
        'images' => [['src' => '/one.png', 'alt' => 'One'], ['src' => '/two.png', 'alt' => 'Post image']],
        'preview' => '',
    ]);
});

it('moves the stored preview without treating its thumbnail as a post image', function (): void {
    $card = '<div id="link-preview-card" data-url="https://example.com"><a href="https://example.com"><img src="/preview.png"></a></div>';

    $content = (new FeedContent)->parse('before'.$card.'after');

    expect($content)->toBe(['html' => 'beforeafter', 'images' => [], 'preview' => $card]);
});

it('prefers uploaded images to previews and ignores duplicates and missing sources', function (): void {
    $content = (new FeedContent)->parse('<img><img src="/one.png"><img src="/one.png"><div id="link-preview-card"><img src="/preview.png"></div>');

    expect($content)->toBe(['html' => '', 'images' => [['src' => '/one.png', 'alt' => 'Post image']], 'preview' => '']);
});

it('retains only the already-selected first preview if multiple stored cards exist', function (): void {
    $first = '<div id="link-preview-card" data-url="https://first.test"></div>';

    $content = (new FeedContent)->parse($first.'<div id="link-preview-card" data-url="https://second.test"></div>');

    expect($content)->toBe(['html' => '', 'images' => [], 'preview' => $first]);
});

it('does not extract image-shaped code and preserves escaped user input', function (): void {
    $html = '<pre><code><img src="/code.png"></code></pre>&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;';

    $content = (new FeedContent)->parse($html);

    expect($content)->toBe(['html' => '<pre><code><img src="/code.png"></code></pre>&lt;script&gt;alert("x")&lt;/script&gt;', 'images' => [], 'preview' => '']);
});

it('renders an empty or media-free post without adding markup', function (string $html): void {
    expect((new FeedContent)->parse($html))->toBe(['html' => $html, 'images' => [], 'preview' => '']);
})->with(['', 'Plain text', '<a href="/@ada">@ada</a>']);

it('removes empty image lines without removing paragraph breaks', function (string $html, string $expected): void {
    expect((new FeedContent)->parse($html)['html'])->toBe($expected);
})->with([
    'images between lines' => ['one<br><img src="/one.png"><br>two<br><img src="/two.png"><br>three<br><img src="/three.png">', 'one<br>two<br>three'],
    'intentional paragraphs' => ['before<br><br>paragraph<br><img src="/one.png">', 'before<br><br>paragraph'],
    'image before text' => ['<img src="/one.png"><br>after', 'after'],
    'media-only post' => ['<img src="/one.png"><br><img src="/two.png"><br>', ''],
    'inline image' => ['before<img src="/one.png"><br>after', 'before<br>after'],
    'whitespace around image' => ["<br> \n<img src=\"/one.png\"> \n<br>after<br> \n", 'after'],
    'media-free line breaks' => ['<br>before<br><br>after<br>', '<br>before<br><br>after<br>'],
]);
