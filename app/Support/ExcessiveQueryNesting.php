<?php

declare(strict_types=1);

namespace App\Support;

use ErrorException;
use Throwable;

/**
 * Detects the warning PHP raises when a query string nests deeper than
 * `max_input_nesting_level`.
 *
 * Symfony's `HeaderUtils::parseQuery()` passes the raw query string straight to
 * `parse_str()`, so any request that nests deeply enough makes PHP warn, and Laravel
 * turns that warning into an exception. PHP only emits the warning when
 * `display_errors` is off, which is why this is invisible locally and a 500 in
 * production.
 */
final readonly class ExcessiveQueryNesting
{
    /**
     * Determine whether the given throwable is the over-nested query string warning.
     */
    public static function matches(Throwable $throwable): bool
    {
        if (! $throwable instanceof ErrorException) {
            return false;
        }

        return str_starts_with($throwable->getMessage(), 'parse_str(): Input variable nesting level exceeded');
    }
}
