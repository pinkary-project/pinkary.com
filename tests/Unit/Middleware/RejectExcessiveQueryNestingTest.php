<?php

declare(strict_types=1);

use App\Http\Middleware\RejectExcessiveQueryNesting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

test('rejects a query string nested deeper than PHP allows', function (): void {
    Route::get('/test-query-nesting', fn (): Response => response(status: 200));

    $this->get('/test-query-nesting?z'.str_repeat('[a]', 70).'=1')
        ->assertStatus(400);
});

test('rejects over-nested percent-encoded brackets', function (): void {
    Route::get('/test-query-nesting-encoded', fn (): Response => response(status: 200));

    $this->get('/test-query-nesting-encoded?z'.str_repeat('%5Ba%5D', 70).'=1')
        ->assertStatus(400);
});

test('allows a query string within the nesting limit', function (): void {
    Route::get('/test-query-nesting-allowed', fn (): Response => response(status: 200));

    $this->get('/test-query-nesting-allowed?z'.str_repeat('[a]', 10).'=1&page=2')
        ->assertOk();
});

test('allows a query string with many sibling parameters', function (): void {
    Route::get('/test-query-nesting-siblings', fn (): Response => response(status: 200));

    $query = collect(range(1, 100))
        ->map(fn (int $index): string => "tag[{$index}]=value")
        ->implode('&');

    $this->get('/test-query-nesting-siblings?'.$query)
        ->assertOk();
});

test('allows requests without a query string', function (): void {
    Route::get('/test-query-nesting-empty', fn (): Response => response(status: 200));

    $this->get('/test-query-nesting-empty')
        ->assertOk();
});

test('rejects on real routes without a server error', function (): void {
    $response = $this->get('/?z'.str_repeat('[a]', 70).'=1');

    expect($response->getStatusCode())->toBe(400);
});

test('the middleware only reads the raw query string', function (): void {
    $middleware = new RejectExcessiveQueryNesting;

    $request = Illuminate\Http\Request::create('/?z[a][a]=1');

    $response = $middleware->handle($request, fn (): Symfony\Component\HttpFoundation\Response => response(status: 200));

    expect($response->getStatusCode())->toBe(200);
});
