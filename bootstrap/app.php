<?php

declare(strict_types=1);

use App\Http\Middleware\TimezoneConfigAssignment;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', TimezoneConfigAssignment::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
