<?php

declare(strict_types=1);

use App\Models\User;
use App\Rules\Username;

test('username validation passes for valid usernames', function (string $username) {
    $rule = new Username();

    $rule->validate('username', $username, fn (string $errorMessage) => $this->fail($errorMessage));

    expect(true)->toBeTrue();
})->with([
    'valid_username',
    'User123',
    '_underscore',
]);

test('username validation fails for invalid usernames', function (string $username) {
    $rule = new Username();

    $fail = fn (string $errorMessage) => throw new InvalidArgumentException($errorMessage);

    $rule->validate('username', $username, $fail);
})->with([
    'invalid username',
    'invalid@username',
    'username!',
    '12345$',
    '-',
    '   ',
])->throws(InvalidArgumentException::class);

test('username validation fails for reserved usernames', function () {
    $user = User::factory()->create();

    $rule = new Username($user);

    $reservedUsername = 'admin'; // Example of a reserved username

    $fail = fn (string $errorMessage) => throw new InvalidArgumentException($errorMessage);

    $rule->validate('username', $reservedUsername, $fail);
})->throws(InvalidArgumentException::class, 'The :attribute is reserved.');

test('username validation fails for existing usernames', function () {
    User::factory()->create(['username' => 'existingUser']);

    $rule = new Username();

    $fail = fn (string $errorMessage) => throw new InvalidArgumentException($errorMessage);

    $rule->validate('username', 'existingUser', $fail);
})->throws(InvalidArgumentException::class, 'The :attribute has already been taken.');
