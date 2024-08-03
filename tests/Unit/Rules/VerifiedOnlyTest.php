<?php

declare(strict_types=1);

use App\Models\User;
use App\Rules\VerifiedOnly;

test('verified only rule passes for verified user', function () {
    $user = User::factory()->create(['is_verified' => true]);

    $this->actingAs($user);

    $rule = new VerifiedOnly();

    $rule->validate('verified', true, fn (string $errorMessage) => $this->fail($errorMessage));

    expect(true)->toBeTrue();
});

test('verified only rule passes for company verified user', function () {
    $user = User::factory()->create(['is_company_verified' => true]);

    $this->actingAs($user);

    $rule = new VerifiedOnly();

    $rule->validate('verified', true, fn (string $errorMessage) => $this->fail($errorMessage));

    expect(true)->toBeTrue();
});

test('verified only rule fails for unverified user', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $rule = new VerifiedOnly();

    $fail = fn (string $errorMessage) => throw new InvalidArgumentException($errorMessage);

    $rule->validate('verified', true, $fail);
})->throws(InvalidArgumentException::class, 'This action is only available to verified users. Get verified in your profile settings.');

test('verified fails for guest', function () {
    $rule = new VerifiedOnly();

    $fail = fn (string $errorMessage) => throw new InvalidArgumentException($errorMessage);

    $rule->validate('verified', true, $fail);
})->throws(InvalidArgumentException::class, 'This action is only available to verified users. Get verified in your profile settings.');
