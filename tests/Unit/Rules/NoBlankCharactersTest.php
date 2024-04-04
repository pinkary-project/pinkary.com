<?php

declare(strict_types=1);

use App\Rules\NoBlankCharacters;

test('with blank characters', function (string $name) {
    $rule = new NoBlankCharacters;

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('name', $name, $fail);

    expect(true)->toBeFalse();
})->with([
    "\u{200E}",
    "\u{200E}\u{200E}",
    "Test\u{200E}User",
    "Test User \u{200E}",
    "\u{200E}Test User",
    "Test 1\u{200E}",
    "测试\u{200E}",
    "ⓣⓔⓢⓣ\u{200E}",
    '  ',
])->fails();

test('without blank characters', function (string $name) {
    $rule = new NoBlankCharacters;

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('name', $name, $fail);

    expect(true)->toBeTrue();
})->with([
    'Nuno Maduro',
    'Taylor Otwell',
]);
