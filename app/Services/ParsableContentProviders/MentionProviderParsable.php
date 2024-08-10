<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;

final readonly class MentionProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace_callback(
            '/(<(a|code|pre)\s+[^>]*>.*?<\/\2>)|@([a-z0-9_]+)/is',
            fn (array $matches): string => $matches[1] !== ''
                ? $matches[1]
                : '<a href="/@'.$matches[3].'" data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>@'.$matches[3].'</a>',
            $content
        );
    }
}
