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
            static function (array $match): string {
                $url = Storage::disk()->url($match[2]);

                return "<img class='object-contain mx-auto w-full rounded-lg' src=\"{$url}\" alt=\"image\" onerror=\"this.outerHTML='<span>...</span>'\">";
            },
            $content
        );
    }
}
