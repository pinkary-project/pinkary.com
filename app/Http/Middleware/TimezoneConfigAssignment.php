<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TimezoneConfigAssignment
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var string $timezone */
        $timezone = session()->get('timezone', 'UTC');

        date_default_timezone_set($timezone);

        Carbon::setLocale($timezone);

        config(['app.timezone' => $timezone]);

        return $next($request);
    }
}
