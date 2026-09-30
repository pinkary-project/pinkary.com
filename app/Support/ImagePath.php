<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Reduce a stored reference to an uploaded image to its disk-relative path.
 *
 * The composer stores `images/2026-09-29/x.jpg` while the API hands clients an
 * absolute URL to embed, so both forms name the same object and have to
 * resolve to the same key.
 */
final readonly class ImagePath
{
    /**
     * Normalize any reference to an uploaded image to `images/...`, or leave
     * one that names no upload of ours alone.
     */
    public static function toRelative(string $reference): string
    {
        $path = parse_url($reference, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            $path = $reference;
        }

        $position = mb_strpos($path, 'images/');

        if ($position === false) {
            return mb_ltrim($path, '/');
        }

        return mb_substr($path, $position);
    }

    /**
     * Whether the reference is already an absolute URL.
     */
    public static function isAbsolute(string $reference): bool
    {
        return (bool) preg_match('#^https?://#i', mb_trim($reference));
    }
}
