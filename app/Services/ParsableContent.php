<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\ParsableContentProvider;
use App\Services\ParsableContentProviders\BrProviderParsable;
use App\Services\ParsableContentProviders\CodeProviderParsable;
use App\Services\ParsableContentProviders\HashtagProviderParsable;
use App\Services\ParsableContentProviders\ImageProviderParsable;
use App\Services\ParsableContentProviders\LinkProviderParsable;
use App\Services\ParsableContentProviders\MentionProviderParsable;
use App\Services\ParsableContentProviders\StripProviderParsable;

final class ParsableContent
{
    /**
     * The cache of parsed content.
     *
     * @var array<string, string>
     */
    private static array $cache = [];

    /**
     * Creates a new parsable content instance.
     *
     * @param  array<int, class-string<ParsableContentProvider>>  $providers
     */
    public function __construct(private readonly array $providers = [
        StripProviderParsable::class,
        CodeProviderParsable::class,
        ImageProviderParsable::class,
        BrProviderParsable::class,
        LinkProviderParsable::class,
        MentionProviderParsable::class,
        HashtagProviderParsable::class,
    ])
    {
        //
    }

    /**
     * Flushes the cache for the given content.
     */
    public static function flush(?string $key = null, ?bool $all = null): void
    {
        if ($key === null && $all === true) {
            self::$cache = [];

            return;
        }

        unset(self::$cache[$key]);
    }

    /**
     * Parses the given content.
     */
    public static function parse(string $key, string $content): ?string
    {
        if (self::has($key)) {
            return self::get($key);
        }

        return self::$cache[$key] = (new self())->parseContent($content);
    }

    /**
     * Checks if the cache has the given key.
     */
    public static function has(string $key): bool
    {
        return isset(self::$cache[$key]);
    }

    /**
     * Gets the cache for the given key.
     */
    public static function get(string $key): ?string
    {
        return self::$cache[$key] ?? null;
    }

    /**
     * Gets the providers.
     *
     * @return array<int, class-string<ParsableContentProvider>>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Parses the content using the providers.
     */
    public function parseContent(string $content): string
    {
        return (string) collect($this->providers)
            ->reduce(function (string $parsed, string $provider): string {
                $provider = new $provider();

                return $provider->parse($parsed);
            }, $content);
    }
}
