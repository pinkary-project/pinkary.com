<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\ParsableContentProvider;
use App\Services\ParsableContentProviders\HashtagProviderParsable;
use App\Services\ParsableContentProviders\MentionProviderParsable;
use App\Services\ParsableContentProviders\StripProviderParsable;

final readonly class ParsableBio
{
    /**
     * Creates a new parsable bio instance.
     *
     * @param  array<int, class-string<ParsableContentProvider>>  $providers
     */
    public function __construct(private array $providers = [
        StripProviderParsable::class,
        MentionProviderParsable::class,
        HashtagProviderParsable::class,
    ]) {}

    /**
     * Parses the given content.
     */
    public function parse(string $content): string
    {
        if ($content === '') {
            return '';
        }

        return (new ParsableContent($this->providers))->parse($content);
    }
}
