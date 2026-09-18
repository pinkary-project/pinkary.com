<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class OptionalSanctum
{
    /**
     * Resolve a bearer token when present, while allowing guests through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('sanctum');

        $request->setUserResolver(function (): ?Authenticatable {
            return Auth::guard('sanctum')->user();
        });

        return $next($request);
    }
}
