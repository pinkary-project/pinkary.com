<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\ParsableContentProvider;

final readonly class BrProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace('/\n/', '</br>', $content);
    }
}
