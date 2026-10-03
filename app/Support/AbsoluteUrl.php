<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

final readonly class AbsoluteUrl
{
    /** Normalize a URL for the requesting client. */
    public static function for(mixed $url, Request $request): ?string
    {
        if (! is_string($url) || mb_trim($url) === '') {
            return null;
        }

        $url = mb_trim($url);

        if (str_starts_with($url, '//')) {
            $url = $request->getScheme().':'.$url;
        }

        if (! preg_match('#^https?://#i', $url)) {
            return self::baseUrl($request).'/'.mb_ltrim($url, '/');
        }

        $host = (string) parse_url($url, PHP_URL_HOST);
        $appHost = self::appHost();
        $requestHost = $request->getHost();

        $unreachable = $host === '' || in_array(mb_strtolower($host), ['localhost', '127.0.0.1', '::1'], true);

        if (! $unreachable && $appHost !== '' && $host === $appHost && $host !== $requestHost) {
            $unreachable = true;
        }

        if (! $unreachable) {
            return $url;
        }

        if (! self::isTrustedRequestHost($request)) {
            return $url;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        return mb_rtrim($request->root(), '/').$path.($query !== null && $query !== '' ? '?'.$query : '');
    }

    /** Resolve an embedded URL relative to its page. */
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

        $host = (string) parse_url($url, PHP_URL_HOST);
        $appHost = self::appHost();

        if ($host !== '' && $appHost !== '' && $host === $appHost) {
            return self::for($url, $request);
        }

        return $url;
    }

    /** Resolve the trusted request or application origin. */
    private static function baseUrl(Request $request): string
    {
        if (self::isTrustedRequestHost($request)) {
            return mb_rtrim($request->root(), '/');
        }

        $appUrl = config('app.url');

        return is_string($appUrl) && $appUrl !== ''
            ? mb_rtrim($appUrl, '/')
            : mb_rtrim($request->root(), '/');
    }

    /** Only rewrite trusted hosts; proxy headers can influence the request root. */
    private static function isTrustedRequestHost(Request $request): bool
    {
        $host = mb_strtolower($request->getHost());

        if ($host === '') {
            return false;
        }

        return in_array($host, array_map(
            static fn (string $value): string => mb_strtolower(mb_trim($value)),
            [self::appHost(), 'localhost', '127.0.0.1', '::1'],
        ), true);
    }

    /** The host this application is served from, or an empty string when unset. */
    private static function appHost(): string
    {
        $url = config('app.url');

        if (! is_string($url)) {
            return '';
        }

        return (string) (parse_url($url, PHP_URL_HOST) ?: '');
    }
}
