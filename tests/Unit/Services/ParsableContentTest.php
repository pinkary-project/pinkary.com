<?php

declare(strict_types=1);

use App\Services\ParsableContent;
use App\Services\ParsableContentProviders\BrProviderParsable;
use App\Services\ParsableContentProviders\CodeProviderParsable;
use App\Services\ParsableContentProviders\HashtagProviderParsable;
use App\Services\ParsableContentProviders\ImageProviderParsable;
use App\Services\ParsableContentProviders\LinkProviderParsable;
use App\Services\ParsableContentProviders\MentionProviderParsable;
use App\Services\ParsableContentProviders\StripProviderParsable;

covers(ParsableContent::class);

test('link', function () {
    $content = 'Sure, here is the link: example.com. Let me know if you have any questions.';

    $provider = new ParsableContent();

    expect($provider->parseContent($content))->toBe('Sure, here is the link: <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>. Let me know if you have any questions.');
});

test('mention', function () {
    $content = '@nunomaduro, let me know if you have any questions. Thanks @xiCO2k.';

    $provider = new ParsableContent();

    expect($provider->parseContent($content))->toBe('<a href="/@nunomaduro" data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>@nunomaduro</a>, let me know if you have any questions. Thanks <a href="/@xiCO2k" data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>@xiCO2k</a>.');
});

it('ignores mention inside <a>', function () {
    $content = 'https://pinkary.com/@nunomaduro';

    $provider = new ParsableContent();

    expect($provider->parseContent($content))->toBe('<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://pinkary.com/@nunomaduro">pinkary.com/@nunomaduro</a>');
});

it('has all the parsable providers in array', function () {
    $provider = new ParsableContent();

    expect($provider->getProviders())->toBe([
        StripProviderParsable::class,
        CodeProviderParsable::class,
        ImageProviderParsable::class,
        BrProviderParsable::class,
        LinkProviderParsable::class,
        MentionProviderParsable::class,
        HashtagProviderParsable::class,
    ]);
});

it('can flush all the cache when the application is terminating', function () {
    $provider = new ParsableContent();

    $provider::parse('key', 'content');
    $provider::parse('key2', 'content2');

    expect($provider::has('key'))->toBeTrue()
        ->and($provider::has('key2'))->toBeTrue();

    $provider::flush(all: true);

    expect($provider::has('key'))->toBeFalse()
        ->and($provider::has('key2'))->toBeFalse();
});

it('can flush the cache for the given key', function () {
    $provider = new ParsableContent();

    $provider::parse('key', 'content');
    $provider::parse('key2', 'content2');

    expect($provider::has('key'))->toBeTrue()
        ->and($provider::has('key2'))->toBeTrue();

    $provider::flush('key');

    expect($provider::has('key'))->toBeFalse()
        ->and($provider::has('key2'))->toBeTrue();
});

it('can parse the content', function () {
    $provider = new ParsableContent();

    expect($provider::parse('key', 'content'))->toBe('content');
});
