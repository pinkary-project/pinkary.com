<?php

declare(strict_types=1);

use App\Rules\NoEmailAlias;

test('validation fails for emails containing aliases', function (string $email): void {
    $rule = new NoEmailAlias;

    $errorMessage = null;
    $fail = function (string $message) use (&$errorMessage): void {
        $errorMessage = $message;
    };

    $rule->validate('email', $email, $fail);

    expect($errorMessage)->toBe('The :attribute cannot contain an email alias.');
})->with([
    'username+new@gmail.com',
    'username+alias@example.com',
    'taylor+test@laravel.com',
    'user.name+tag@example.co.uk',
    '+user@example.com',
    'user+@example.com',
]);

test('validation passes for emails without aliases', function (string $email): void {
    $rule = new NoEmailAlias;

    $failed = false;
    $fail = function () use (&$failed): void {
        $failed = true;
    };

    $rule->validate('email', $email, $fail);

    expect($failed)->toBeFalse();
})->with([
    'username@gmail.com',
    'taylor@laravel.com',
    'user.name@example.co.uk',
    'admin@example.com',
]);

test('validation ignores non-string values', function (mixed $value): void {
    $rule = new NoEmailAlias;

    $failed = false;
    $fail = function () use (&$failed): void {
        $failed = true;
    };

    $rule->validate('email', $value, $fail);

    expect($failed)->toBeFalse();
})->with([
    null,
    123,
    [['email' => 'test@example.com']],
]);
