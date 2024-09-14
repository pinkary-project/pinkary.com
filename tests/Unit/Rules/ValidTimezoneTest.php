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

test('a deprecated timezone must be valid attempt 1', function () {
    $response = $this->post(route('profile.timezone.update'), [
        'timezone' => 'Asia/Calcutta',
    ]);

    $response->assertStatus(200);
});

test('a deprecated timezone must be valid attempt 2', function () {
    $response = $this->post(route('profile.timezone.update'), [
        'timezone' => 'Asia/Katmandu',
    ]);

    $response->assertStatus(200);
});
