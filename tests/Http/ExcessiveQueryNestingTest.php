<?php

declare(strict_types=1);

use App\Support\ExcessiveQueryNesting;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    Route::get('/query-nesting-probe', fn (): Response => response(status: 200));
});

/**
 * Send a request whose query string nests deeper than PHP allows.
 *
 * PHP only raises the nesting warning when display_errors is off, which is the case in
 * production. Building the request with display_errors explicitly on keeps PHP from
 * warning during construction, so the warning is raised later — inside the kernel, on the
 * same `Request::getQueryString()` call that global middleware makes — which is where
 * production actually fails. Both values are set explicitly rather than inherited, because
 * CI runs with display_errors off already and would otherwise warn too early to be handled.
 */
function handleWithOverNestedQueryString(Container $app, string $uri): Response
{
    $original = ini_set('display_errors', '1');

    try {
        $request = Request::create($uri);
    } finally {
        ini_set('display_errors', (string) $original);
    }

    $previous = ini_set('display_errors', '0');

    try {
        return $app->make(Kernel::class)->handle($request);
    } finally {
        ini_set('display_errors', (string) $previous);
    }
}

test('rejects an over-nested query string with a 400', function (): void {
    $response = handleWithOverNestedQueryString($this->app, '/query-nesting-probe?z'.str_repeat('[a]', 70).'=1');

    expect($response->getStatusCode())->toBe(400);
});

test('rejects an over-nested parameter that has no value', function (): void {
    $response = handleWithOverNestedQueryString($this->app, '/query-nesting-probe?z'.str_repeat('[a]', 70));

    expect($response->getStatusCode())->toBe(400);
});

test('allows a query string nested within the php limit', function (): void {
    $response = handleWithOverNestedQueryString($this->app, '/query-nesting-probe?z'.str_repeat('[a]', 10).'=1');

    expect($response->getStatusCode())->toBe(200);
});

test('allows a query string nested at exactly the php limit', function (): void {
    $response = handleWithOverNestedQueryString($this->app, '/query-nesting-probe?z'.str_repeat('[a]', 64).'=1');

    expect($response->getStatusCode())->toBe(200);
});

test('allows a request without a query string', function (): void {
    $response = handleWithOverNestedQueryString($this->app, '/query-nesting-probe');

    expect($response->getStatusCode())->toBe(200);
});

test('does not report the nesting warning as an application error', function (): void {
    $handler = app(Illuminate\Contracts\Debug\ExceptionHandler::class);

    $exception = new ErrorException(
        'parse_str(): Input variable nesting level exceeded 64. To increase the limit change max_input_nesting_level in php.ini.',
        0,
        E_WARNING,
        'vendor/symfony/http-foundation/HeaderUtils.php',
        244,
    );

    expect(ExcessiveQueryNesting::matches($exception))->toBeTrue();
});
