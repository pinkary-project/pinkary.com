<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;
use Illuminate\Support\Str;

final readonly class HashtagProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace_callback(
            '/(<(a|code|pre)\s+[^>]*>.*?<\/\2>)|(?<!&)#([a-z0-9]+)/is',
            function (array $matches): string {
                if ($matches[1] !== '') {
                    return $matches[1];
                }

                $sanitizedHashtag = Str::limit($matches[3], 200, '');

                return sprintf(
                    '<a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" href="%s">#%s</a>',
                    "/hashtag/{$sanitizedHashtag}",
                    $sanitizedHashtag
                );
            },
            $content
        );
    }
}
