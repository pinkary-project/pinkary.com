<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Reduce a stored reference to an uploaded image to its disk-relative path.
 *
 * The composer writes `images/2026-09-29/x.jpg` into a post, but the mobile
 * API hands clients an absolute URL to embed, so a post written from the app
 * carries `https://cdn.example.com/images/2026-09-29/x.jpg`. Both name the
 * same object, so both have to resolve to the same key:
 *
 * - Storage::url() prepends the bucket URL, which turns an already-absolute
 *   reference into `https://cdn/https://cdn/images/...` -- a broken <img>.
 * - CleanUnusedUploadedImages compares its file list against the references
 *   with a strict in_array, so an absolute reference never matches and the
 *   hourly cleanup deletes a file the post still points at.
 */
final readonly class ImagePath
{
    /**
     * Normalize any reference to an uploaded image to `images/...`.
     *
     * References that do not name one of our uploads are returned with only
     * a leading slash trimmed, so an external image is never mistaken for a
     * local one.
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
