<?php

declare(strict_types=1);

test('link', function () {
    $content = 'Sure, here is the link: example.com. Let me know if you have any questions.';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('<p>Sure, here is the link: <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>. Let me know if you have any questions.</p>');
});

test('mention', function () {
    $content = '@nunomaduro, let me know if you have any questions. Thanks @xiCO2k.';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('<p><a href="/@nunomaduro" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>@nunomaduro</a>, let me know if you have any questions. Thanks <a href="/@xiCO2k" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>@xiCO2k</a>.</p>');
});

it('ignores mention inside <a>', function () {
    $content = 'https://pinkary.com/@nunomaduro';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('<p><a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://pinkary.com/@nunomaduro">pinkary.com/@nunomaduro</a></p>');
});

test('consecutive code blocks with text', function () {
    $content = <<<'EOT'
    Hi this is the first code block:
    ```php
    $user = User::find(1);
    ```
    And this is the second code block:
    ```php
    $user->delete();
    ```
    And a final link to the documentation: https://example.com
    EOT;

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('<p>Hi this is the first code block:</p><pre><code class="p-4 rounded-lg hljs php text-xs" style="background-color: #23262E">$user = User::find(<span class="hljs-number">1</span>);</code></pre><p>And this is the second code block:</p><pre><code class="p-4 rounded-lg hljs php text-xs" style="background-color: #23262E">$user-&gt;delete();</code></pre><p>And a final link to the documentation: <a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a></p>');
});
