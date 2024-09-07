<?php

declare(strict_types=1);

use App\Models\User;
use App\Rules\Username;

test('username validation passes for valid usernames', function () {
    $rule = new Username();

    $validUsernames = [
        'valid_username',
        'User123',
        '_underscore',
    ];

    foreach ($validUsernames as $username) {
        $rule->validate('username', $username, fn(string $errorMessage) => $this->fail($errorMessage));
    }

    expect(true)->toBeTrue();
});

test('username validation fails for invalid usernames', function () {
    $rule = new Username();

    $invalidUsernames = [
        'invalid username', // contains space
        'invalid@username', // contains special character
        'username!',        // contains special character
        '12345$',          // contains special character
        '-',                // just a hyphen
        '   ',             // whitespace
        'us',              // less than minimum length
    ];

    foreach ($invalidUsernames as $username) {
        $fail = fn(string $errorMessage) => throw new InvalidArgumentException($errorMessage);

        $rule->validate('username', $username, $fail);
    }
})->throws(InvalidArgumentException::class);

test('username validation fails for reserved usernames', function () {
    $user = User::factory()->create();

    $rule = new Username($user);

    $fail = fn(string $errorMessage) => throw new InvalidArgumentException($errorMessage);

    $rule->validate('username', 'administrator', $fail);
})->throws(InvalidArgumentException::class, 'The username is reserved.');

test('username validation fails for existing usernames', function () {
    User::factory()->create(['username' => 'existingUser']);

    $rule = new Username();

    $fail = fn(string $errorMessage) => throw new InvalidArgumentException($errorMessage);

    $rule->validate('username', 'existingUser', $fail);
})->throws(InvalidArgumentException::class, 'The username has already been taken.');
