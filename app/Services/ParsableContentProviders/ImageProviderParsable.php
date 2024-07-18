<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;
use Illuminate\Support\Facades\Storage;

final readonly class ImageProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace_callback(
            '/!\[(.*?)\]\((.*?)\)/',
            static function ($match): string {
                $altText = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '', $match[1]);

                $url = !filter_var($match[2], FILTER_VALIDATE_URL) ? Storage::url($match[2]) : $match[2];

                return "<img class='object-cover mx-auto max-h-[52rem] w-full max-w-[26rem] rounded-lg' src=\"{$url}\" alt=\"{$altText}\">";
            },
            $content
        );
    }
}
