<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Rewrite stored URLs into absolute ones the requesting client can reach.
 *
 * Stored references are app-relative or built from APP_URL, but a phone on
 * the local network reaches the API through another host entirely, so they
 * come back unreachable and images render blank. Rewriting is limited to
 * APP_TRUSTED_HOSTS so a forged Host header cannot repoint a response's
 * media at an origin of the caller's choosing.
 */
final readonly class AbsoluteUrl
{
    /**
     * Normalize an avatar/image URL for the requesting client.
     */
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

        $host = (string) parse_url($url, PHP_URL_HOST);
        $appHost = self::appHost();

        if ($host !== '' && $appHost !== '' && $host === $appHost) {
            return self::for($url, $request);
        }

        return $url;
    }

    /**
     * The origin a relative reference resolves against: the request's own
     * root on a trusted host, APP_URL otherwise.
     *
     * Relative references are the common case -- the local public disk hands
     * out `/storage/...` -- so resolving them against an untrusted Host
     * header would repoint every image in the response just as surely as
     * rewriting an absolute one.
     */
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

    /**
     * Whether the request arrived on a host this application serves: APP_URL's
     * own host, the local development names, or APP_TRUSTED_HOSTS.
     *
     * The application trusts every proxy, so the request root reflects
     * X-Forwarded-Host as well as Host. Rewriting onto it is therefore only
     * safe for a host this application is meant to be reached on.
     */
    private static function isTrustedRequestHost(Request $request): bool
    {
        $host = mb_strtolower($request->getHost());

        if ($host === '') {
            return false;
        }

        $allowed = [self::appHost(), 'localhost', '127.0.0.1', '::1'];

        $configured = config('trusted-hosts.hosts');

        if (is_array($configured)) {
            foreach ($configured as $candidate) {
                if (is_string($candidate)) {
                    $allowed[] = $candidate;
                }
            }
        }

        $allowed = array_filter(array_map(
            static fn (string $value): string => mb_strtolower(mb_trim($value)),
            $allowed,
        ));

        return in_array($host, $allowed, true);
    }

    /**
     * The host this application is served from, or an empty string when unset.
     */
    private static function appHost(): string
    {
        $url = config('app.url');

        if (! is_string($url)) {
            return '';
        }

        return (string) (parse_url($url, PHP_URL_HOST) ?: '');
    }
}
