<?php

declare(strict_types=1);

use App\Rules\NoBlankCharacters;

test('with blank characters', function () {
    $rule = new NoBlankCharacters;

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('name', '\u{200E}', $fail);

    expect(true)->toBeFalse();
})->fails();

test('without blank characters', function () {
    $rule = new NoBlankCharacters;

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('name', 'foo', $fail);

    expect(true)->toBeTrue();
});
