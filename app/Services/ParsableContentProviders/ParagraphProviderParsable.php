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
            function (array $matches): string {
                $isCodeBlock = preg_match('/^<pre><code.*?<\/code><\/pre>$/', $matches[1]);
                $isEmpty = empty($matches[1]);

                return $isCodeBlock || $isEmpty
                    ? $matches[1]
                    : '<p>'.$matches[1].'</p>';
            },
            $content);
    }
}
