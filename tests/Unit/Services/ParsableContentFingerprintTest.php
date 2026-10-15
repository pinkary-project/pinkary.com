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

$providers = [
    StripProviderParsable::class,
    CodeProviderParsable::class,
    ImageProviderParsable::class,
    BrProviderParsable::class,
    LinkProviderParsable::class,
    MentionProviderParsable::class,
    HashtagProviderParsable::class,
];

test('a stale persisted fingerprint never wins over the current provider files', function () use ($providers): void {
    $key = 'parsable-fingerprint-'.hash('sha256', implode(',', $providers));
    Cache::put($key, 'a-stale-fingerprint', now()->addYear());

    $parsable = new ParsableContent($providers);

    expect($parsable->fingerprint())->not->toBe('a-stale-fingerprint');
});

test('the fingerprint matches the current source of the parse pipeline', function () use ($providers): void {
    $hashes = [];

    foreach ([...$providers, App\Services\MetaData::class, ParsableContent::class] as $class) {
        $hashes[] = md5_file(new ReflectionClass($class)->getFileName());
    }

    $hashes[] = md5_file(resource_path('views/components/link-preview-card.blade.php'));

    expect(new ParsableContent($providers)->fingerprint())
        ->toBe(hash('sha256', (string) json_encode($hashes)));
});

test('the fingerprint is not written to the cache store', function () use ($providers): void {
    $key = 'parsable-fingerprint-'.hash('sha256', implode(',', $providers));

    new ParsableContent($providers)->fingerprint();

    expect(Cache::get($key))->toBeNull();
});
