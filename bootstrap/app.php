<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Env;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustHosts();

        // Which proxies may set X-Forwarded-* headers. '*' trusts every peer,
        // which makes $request->ip() the left-most client-supplied
        // X-Forwarded-For value rather than the real address. Everything
        // keyed on the IP is then attacker-controlled: every throttle in
        // routes/api.php, including throttle:login, and the per-IP-per-day
        // link click dedupe. A caller can defeat all of them by varying the
        // header per request.
        //
        // Over-narrowing is wrong too: Laravel then ignores
        // X-Forwarded-Proto, breaking HTTPS detection, secure cookies and
        // generated URLs. Check what is actually in front before changing it.
        //
        // Read through the Env repository rather than config(), because this
        // closure runs inside Application::configure() -- before the config
        // repository is bound, so config() does not exist yet here. The
        // runtime behaviour is identical to env(): with the config cached,
        // .env is not loaded and this resolves from the real process
        // environment.
        $trustedProxies = Env::get('TRUSTED_PROXIES', '*');

        $middleware->trustProxies(at: match (true) {
            is_string($trustedProxies) => $trustedProxies,
            is_array($trustedProxies) => array_values(array_filter($trustedProxies, is_string(...))),
            default => '*',
        });

        $middleware->alias([
            'block.bots' => App\Http\Middleware\BlockBots::class,
            'optional.sanctum' => App\Http\Middleware\OptionalSanctum::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e): bool => $request->is('api/*') || $request->expectsJson());
    })->create();
