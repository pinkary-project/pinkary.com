<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;

final readonly class FormattingProviderParsable implements ParsableContentProvider
{
    /**
     * Parse the supported Markdown-style formatting.
     */
    public function parse(string $content): string
    {
        $parts = preg_split('/(<(?:pre|code|a|img)\b[^>]*>.*?<\/(?:pre|code|a)>|<img\b[^>]*>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return $content;
        }

        return implode('', array_map(function (string $part): string {
            if (preg_match('/^<(?:pre|code|a|img)\b/i', $part) === 1) {
                return $part;
            }

            $part = (string) preg_replace_callback(
                '/\[([^\]\n]+)]\((https?:\/\/[^\s)]+)\)/i',
                static function (array $matches): string {
                    $url = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5);

                    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                        return $matches[0];
                    }

                    return sprintf(
                        '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" rel="noopener noreferrer" href="%s">%s</a>',
                        e($url),
                        $matches[1],
                    );
                },
                $part,
            );

            $part = (string) preg_replace('/\*\*([^*\n]+)\*\*/', '<strong>$1</strong>', $part);
            $part = (string) preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/', '<em>$1</em>', $part);

            return (string) preg_replace(
                '/^&gt;[ \t]?(.*)$/m',
                '<blockquote class="border-l-4 border-slate-300 pl-3 text-slate-600 dark:border-slate-600 dark:text-slate-300">$1</blockquote>',
                $part,
            );
        }, $parts));
    }
}
