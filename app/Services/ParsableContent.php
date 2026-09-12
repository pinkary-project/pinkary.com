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
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

final readonly class ParsableContent
{
    /**
     * Creates a new parsable content instance.
     *
     * @param  array<int, class-string<ParsableContentProvider>>  $providers
     */
    public function __construct(private array $providers = [
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
     * Parses the given content.
     */
    public function parse(string $content): string
    {
        return (string) collect($this->providers)
            ->reduce(function (string $parsed, string $provider): string {
                $provider = new $provider();

                return $provider->parse($parsed);
            }, $content);
    }

    /**
     * Fingerprint of the parser pipeline.
     *
     * Derived from the source of every provider, the orchestration
     * itself, the link metadata parsing, and the preview card markup,
     * so any rendering change automatically stale-marks previously
     * stored parses.
     */
    public function fingerprint(): string
    {
        $key = implode(',', $this->providers);

        /** @var string $fingerprint */
        $fingerprint = Cache::memo()->remember(
            'parsable-fingerprint-'.hash('sha256', $key),
            now()->addYear(),
            function (): string {
                $hashes = [];

                /** @var list<class-string> $classes */
                $classes = [...$this->providers, MetaData::class, self::class];

                foreach ($classes as $class) {
                    $file = new ReflectionClass($class)->getFileName();

                    if (is_string($file)) {
                        $hashes[] = md5_file($file);
                    }
                }

                $blade = md5_file(resource_path('views/components/link-preview-card.blade.php'));

                if (is_string($blade)) {
                    $hashes[] = $blade;
                }

                return hash('sha256', (string) json_encode($hashes));
            }
        );

        return $fingerprint;
    }
}
