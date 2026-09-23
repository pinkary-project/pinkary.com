<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Rewrite stored URLs into absolute ones the requesting client can reach.
 *
 * Avatars and embedded images are stored as app-relative paths or asset
 * URLs built from APP_URL (e.g. https://pinkary.test). A phone on the
 * local network reaches the API through another host entirely, so those
 * URLs come back unreachable and images render blank. Rebuilding them
 * from the request's own root fixes every client without touching the
 * web's asset handling.
 */
final class AbsoluteUrl
{
    /**
     * Normalize an avatar/image URL for the requesting client.
     */
    public static function for(?string $url, Request $request): ?string
    {
        if (! is_string($url) || mb_trim($url) === '') {
            return null;
        }

        $url = mb_trim($url);

        if (str_starts_with($url, '//')) {
            $url = $request->getScheme().':'.$url;
        }

        if (! preg_match('#^https?://#i', $url)) {
            return mb_rtrim($request->root(), '/').'/'.mb_ltrim($url, '/');
        }

        $host = (string) parse_url($url, PHP_URL_HOST);
        $appHost = (string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: '');
        $requestHost = $request->getHost();

        $unreachable = $host === '' || in_array(mb_strtolower($host), ['localhost', '127.0.0.1', '::1'], true);

        if (! $unreachable && $appHost !== '' && $host === $appHost && $host !== $requestHost) {
            $unreachable = true;
        }

        if (! $unreachable) {
            return $url;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        return mb_rtrim($request->root(), '/').$path.($query !== null && $query !== '' ? '?'.$query : '');
    }

    /**
     * Resolve a page-embedded URL (link-preview / content images) against
     * the page it was found on, then normalize it for the client.
     */
    public static function fromPage(?string $url, string $pageUrl, Request $request): ?string
    {
        if (! is_string($url) || mb_trim($url) === '') {
            return null;
        }

        $url = mb_trim($url);

        if (str_starts_with($url, '//')) {
            $url = parse_url($pageUrl, PHP_URL_SCHEME).':'.$url;
        } elseif (str_starts_with($url, '/')) {
            $parts = parse_url($pageUrl);
            $url = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').$url;
        } elseif (! preg_match('#^https?://#i', $url)) {
            $parts = parse_url($pageUrl);
            $base = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').'/'.mb_ltrim((string) ($parts['path'] ?? '/'), '/');
            $url = mb_substr($base, 0, (int) mb_strrpos($base, '/') + 1).$url;
        }

        // Page-embedded uploads served by this app are always reachable
        // through the API host.
        $host = (string) parse_url($url, PHP_URL_HOST);
        $appHost = (string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: '');

        if ($host !== '' && $appHost !== '' && $host === $appHost) {
            return self::for($url, $request);
        }

        return $url;
    }
}
