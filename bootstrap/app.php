<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureUserHasValidRoute;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(EnsureUserHasValidRoute::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
