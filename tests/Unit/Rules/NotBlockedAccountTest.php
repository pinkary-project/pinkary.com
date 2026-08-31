<?php

declare(strict_types=1);

use App\Models\BlockedAccount;
use App\Rules\NotBlockedAccount;

test('passes when email is not blocked', function (): void {
    $rule = new NotBlockedAccount;

    $rule->validate('email', 'clean@example.com', fn (string $errorMessage) => $this->fail($errorMessage));

    expect(true)->toBeTrue();
});

test('fails when email is blocked', function (): void {
    BlockedAccount::factory()->create(['email' => 'blocked@example.com']);

    $rule = new NotBlockedAccount;

    $rule->validate('email', 'blocked@example.com', function (string $errorMessage): void {
        expect($errorMessage)->toBe('This email has been blocked.');
    });
});

test('passes when value is not a string', function (): void {
    $rule = new NotBlockedAccount;

    $rule->validate('email', null, fn (string $errorMessage) => $this->fail($errorMessage));

    expect(true)->toBeTrue();
});
