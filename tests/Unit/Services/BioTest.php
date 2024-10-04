<?php

declare(strict_types=1);


test('xss', function () {
    $content = 'hi I\'m a <b>full stack dev</b> and here is my link <a href="https://example.com">example.com</a>.';

    $provider = new App\Services\ParsableBio();

    expect($provider->parse($content))->toBe('hi I&#039;m a &lt;b&gt;full stack dev&lt;/b&gt; and here is my link &lt;a href=&quot;<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>&quot;&gt;example.com&lt;/a&gt;.');

    $content = 'hi I\'m a <a onload="alert(\'XSS\')">full stack dev</a> and here is my link https://example.com';

    expect($provider->parse($content))->toBe('hi I&#039;m a &lt;a onload=&quot;alert(&#039;XSS&#039;)&quot;&gt;full stack dev&lt;/a&gt; and here is my link <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>');
});

test('empty', function () {
    $content = '';

    $provider = (new App\Services\ParsableBio);

    expect($provider->parse($content))->toBe('');
});
