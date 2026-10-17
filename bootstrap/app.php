<?php

declare(strict_types=1);

use App\Support\ExcessiveQueryNesting;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustHosts();
        $middleware->trustProxies();

        $middleware->alias([
            'block.bots' => App\Http\Middleware\BlockBots::class,
            'optional.sanctum' => App\Http\Middleware\OptionalSanctum::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e): bool => $request->is('api/*') || $request->expectsJson());

        // An over-nested query string is a bad request, not a server fault. PHP only warns
        // when display_errors is off, so this fires in production but never locally.
        $exceptions->dontReportWhen(fn (Throwable $e): bool => ExcessiveQueryNesting::matches($e));

        $exceptions->render(fn (Throwable $e): ResponseFactory|Response|null => ExcessiveQueryNesting::matches($e)
            ? response(status: 400)
            : null);
    })->create();
