<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;
use App\Support\ImagePath;
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
                // A reference the client already made absolute is used as
                // it stands. Storage::url() prepends the bucket URL, which
                // would otherwise turn it into
                // https://cdn/https://cdn/images/... -- the composer stores
                // the bare path, but the mobile API hands out a URL.
                if (ImagePath::isAbsolute($match[2])) {
                    $url = $match[2];
                } else {
                    $url = Storage::disk()->url(ImagePath::toRelative($match[2]));
                }

                return "<img class='object-contain mx-auto w-full rounded-lg' src=\"{$url}\" alt=\"image\" onerror=\"this.outerHTML='<span>...</span>'\">";
            },
            $content
        );
    }
}
