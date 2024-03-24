<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\ParsableContentProvider;

final readonly class ParagraphProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace_callback(
            '/(.*?)(?:\n|$)/s',
            function ($matches) {
                $isNotCodeBlock = ! preg_match('/^<pre><code.*?<\/code><\/pre>$/', $matches[1]);
                $isNotEmpty = ! empty(trim($matches[1]));

                return $isNotEmpty && $isNotCodeBlock ? "<p>{$matches[1]}</p>" : $matches[1];
            },
            $content);
    }
}
