<?php

declare(strict_types=1);

use App\Services\ParsableContentProviders\ImageProviderParsable;
use Illuminate\Support\Facades\Storage;

it('rewrites a bare disk path into an image tag', function (): void {
    Storage::fake();

    $html = (new ImageProviderParsable)->parse('![a](images/2026-09-29/a.jpg)');

    expect($html)
        ->toContain("<img class='object-contain mx-auto w-full rounded-lg'")
        ->toContain(Storage::disk()->url('images/2026-09-29/a.jpg'));
});

it('leaves an already absolute url alone instead of doubling it', function (): void {
    // A post written from the mobile app embeds the absolute URL the API
    // returned. Prepending the bucket URL again produced
    // https://cdn/https://cdn/images/... -- a broken image on the web.
    Storage::fake();

    $absolute = 'https://cdn.example.com/images/2026-09-29/a.jpg';

    $html = (new ImageProviderParsable)->parse("![a]({$absolute})");

    expect($html)->toContain($absolute)
        ->not->toContain('https://cdn.example.com/https://');
});

it('still rewrites a disk rooted reference', function (): void {
    Storage::fake();

    $html = (new ImageProviderParsable)->parse('![a](/storage/images/2026-09-29/a.jpg)');

    expect($html)->toContain(Storage::disk()->url('images/2026-09-29/a.jpg'));
});
