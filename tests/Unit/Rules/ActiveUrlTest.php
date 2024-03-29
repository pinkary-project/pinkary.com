<?php

declare(strict_types=1);

use App\Rules\ActiveUrl;
use Dotenv\Util\Str;

// The tests should be using a dataset that ensures previous links are working as expected.
// don't forget to fake the http stuff on tests.

test('it validates an active url', function (string $url) {
    $rule = new ActiveUrl;

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('url',$url, $fail);

    expect(true)->toBeTrue();
})
->with([
    'https://nunomaduro.com',
    'https://laravel.com',
    'https://github.com',
]);

test('it does not validate an inactive url', function (string $url) {
    $rule = new ActiveUrl;

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('url',$url, $fail);

    expect(true)->toBeFalse();
})->with([
    'https://nunomaduro.com/invalid',
    'https://laravel.com/invalid',
    'https://github.com/invalid',
])->fails();

