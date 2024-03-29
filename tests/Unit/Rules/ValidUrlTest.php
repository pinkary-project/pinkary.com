<?php

declare(strict_types=1);

use App\Rules\ValidUrl;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

test('it validates an active url', function (string $url) {
    Http::fake([
        $url => Http::response(),
    ]);

    $rule = new ValidUrl;

    $fail = fn (string $errorMessage): bool => $this->fail($errorMessage);

    $rule->validate('url', $url, $fail);

    expect(true)->toBeTrue();
})
    ->with([
        'https://nunomaduro.com',
        'https://www.nunomaduro.com',
        'https://laravel.com',
        'https://github.com',
    ]);

test('it does not validate an inactive url', function (string $url) {
    Http::fake([
        $url => Http::response(null, 404),
    ]);

    $rule = new ValidUrl;

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('url', $url, $fail);
})->with([
    'https://nunomaduro.com/invalid',
    'https://laravel.com/invalid',
    'https://github.com/108238102371289371897321',
    'https://qwdqw',
])->fails();

test('it does not validate an inactive url when it cant connect', function (string $url) {
    Http::fake(function () {
        throw new ConnectionException('Connection error');
    });

    $rule = new ValidUrl;

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('url', $url, $fail);
})->with([
    'https://nunomaduro.com/invalid',
    'https://laravel.com/invalid',
    'https://github.com/108238102371289371897321',
    'https://qwdqw',
])->fails();
