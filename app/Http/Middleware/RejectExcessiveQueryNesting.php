<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects query strings nested deeper than PHP's `max_input_nesting_level`.
 *
 * PHP emits a warning when it truncates an over-nested query string, and Laravel turns
 * that warning into an ErrorException from deep inside Symfony's request handling, which
 * surfaces as a 500. Measuring the raw query string ourselves lets us reject the request
 * up front with a 400 instead of letting it blow up halfway through the middleware stack.
 */
final readonly class RejectExcessiveQueryNesting
{
    /**
     * The nesting level PHP allows before it starts truncating the query string.
     */
    private const int DEFAULT_MAX_NESTING_LEVEL = 64;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $queryString = $request->server->get('QUERY_STRING');

        if (is_string($queryString) && $this->isNestedTooDeep($queryString)) {
            return response(status: 400);
        }

        return $next($request);
    }

    /**
     * Determine if any query parameter nests deeper than PHP tolerates.
     *
     * PHP measures the number of brackets in a single parameter name, so `z[a][b]` is
     * nested two levels deep regardless of how many parameters sit next to it.
     */
    private function isNestedTooDeep(string $queryString): bool
    {
        if (! str_contains($queryString, '[') && mb_stripos($queryString, '%5b') === false) {
            return false;
        }

        $limit = $this->maxNestingLevel();

        foreach (explode('&', $queryString) as $pair) {
            $separator = mb_strpos($pair, '=');

            $name = $separator === false ? $pair : mb_substr($pair, 0, $separator);

            if (mb_substr_count(rawurldecode($name), '[') > $limit) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the nesting level PHP is configured with, ignoring "unlimited" values.
     */
    private function maxNestingLevel(): int
    {
        $level = (int) ini_get('max_input_nesting_level');

        return $level > 0 ? $level : self::DEFAULT_MAX_NESTING_LEVEL;
    }
}
