<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\ParsableContentProvider;
use App\Services\ParsableContentProviders\BrProviderParsable;
use App\Services\ParsableContentProviders\CodeProviderParsable;
use App\Services\ParsableContentProviders\ImageProviderParsable;
use App\Services\ParsableContentProviders\LinkProviderParsable;
use App\Services\ParsableContentProviders\MentionProviderParsable;
use App\Services\ParsableContentProviders\StripProviderParsable;

final readonly class ParsableContent
{
    /**
     * Creates a new parsable content instance.
     *
     * @param  array<int, class-string<ParsableContentProvider>>  $providers
     */
    public function __construct(private array $providers = [
        StripProviderParsable::class,
        ImageProviderParsable::class,
        CodeProviderParsable::class,
        BrProviderParsable::class,
        LinkProviderParsable::class,
        MentionProviderParsable::class,
    ])
    {
        //
    }

    /**
     * Parses the given content.
     */
    public function parse(string $content): string
    {
        return (string) collect($this->providers)
            ->reduce(function (string $parsed, string $provider): string {
                $provider = type(new $provider())->as(ParsableContentProvider::class);

                return $provider->parse($parsed);
            }, $content);
    }
}
