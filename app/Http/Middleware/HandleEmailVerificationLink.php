<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class HandleEmailVerificationLink
{
    /**
     * Send valid verification links through login and handle expired links gracefully.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasValidSignature()) {
            return to_route('login')->with(
                'flash-message',
                'This verification link has expired or is invalid. Please log in to request a new one.',
            );
        }

        if (! $request->user()) {
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}
