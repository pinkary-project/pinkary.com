<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureUserHasValidRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = type($request->path())->asString();
        $validator = Validator::make(compact('path'),
            [
                'path' => 'exists:users,username',
            ]
        );
        if ($validator->fails()) {
            return $next($request);
        }

        return redirect()->route('profile.show', ['username' => $path]);
    }
}
