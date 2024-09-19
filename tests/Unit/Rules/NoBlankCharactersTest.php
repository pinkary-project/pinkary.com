<?php

declare(strict_types=1);

use App\Rules\NoBlankCharacters;

test('validation fails for strings containing blank characters', function (string $name) {
    $rule = new NoBlankCharacters;

    $fail = function (string $errorMessage) {
        // Capture the error message
        $this->errorMessage = $errorMessage;
    };

    // Validate the input
    $rule->validate('name', $name, $fail);

    expect(isset($this->errorMessage))->toBeTrue();
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
    "\u{2005}",
    "\u{2006}",
    "\u{2007}",
    "\u{2008}",
    "\u{2009}",
    "\u{200A}",
    "\u{2028}",
    "\u{205F}",
    "\u{3000}",
    " \u{2005} ",
    "\u{2007}\u{2008}\u{2009}",
]);

test('without blank characters', function (string $name) {
    $rule = new NoBlankCharacters;

    $fail = fn (string $errorMessage) => $this->fail($errorMessage);

    $rule->validate('name', $name, $fail);

    expect(true)->toBeTrue();
})->with([
    'Nuno Maduro',
    'Taylor Otwell',
    'ManukMinasyan',
]);
