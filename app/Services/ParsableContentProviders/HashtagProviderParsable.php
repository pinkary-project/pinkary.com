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
            '/(<a\s+[^>]*>.*?<\/a>)|(?<!&)#([a-z0-9_]+)/i',
            fn (array $matches): string => $matches[1] !== ''
                ? $matches[1]
                : '<span class="text-blue-500">#'.$matches[2].'</span>',
            $content
        );
    }
}
