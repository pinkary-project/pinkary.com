<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Firewall;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final readonly class BlockBots
{
    public function __construct(
        private Firewall $firewall,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        if (Auth::check()) {
            return $next($request);
        }

        if ($this->firewall->isBlockedCrawler($request)) {
            return redirect()->route('error.access-denied');
        }

        return $next($request);
    }
}
