<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;

final readonly class HashtagProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace_callback(
            '/(<(a|code|pre)\s+[^>]*>.*?<\/\2>)|(?<!&)#([a-z0-9]+)/is',
            fn (array $matches): string => $matches[1] !== ''
                ? $matches[1]
                : '<span class="text-blue-500">#'.$matches[3].'</span>',
            $content
        );
    }
}
