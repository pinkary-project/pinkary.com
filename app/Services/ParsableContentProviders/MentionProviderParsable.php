<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\ParsableContentProvider;

final readonly class MentionProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace('/@([a-zA-Z0-9_-]+)/', '<a href="@$1" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>@$1</a>', $content);
    }
}
