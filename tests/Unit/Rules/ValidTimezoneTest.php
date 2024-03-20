<?php

declare(strict_types=1);

use App\Rules\ValidTimezone;

test('valid timezone', function (string $timezone) {
    $rule = new ValidTimezone();

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('timezone', $timezone, $fail);

    expect(true)->toBeTrue();
})->with([
    'America/New_York',
    'UTC',
    'Europe/London',
    'Asia/Tokyo',
]);

test('invalid timezone', function (string $timezone) {
    $rule = new ValidTimezone();

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('timezone', $timezone, $fail);

    expect(true)->toBeFalse();
})->with([
    'America/New_Yorkk',
    'UTCC',
    'Europe/Londonn',
    'Asia/Tokyo0',
])->fails();
