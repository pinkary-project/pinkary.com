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

it('resolves the stored path against the configured disk', function (): void {
    Storage::fake();

    $html = (new ImageProviderParsable)->parse('![a](images/2026-09-29/b.jpg)');

    expect($html)
        ->toContain(Storage::disk()->url('images/2026-09-29/b.jpg'))
        ->not->toContain('images/2026-09-29/b.jpg\'');
});
