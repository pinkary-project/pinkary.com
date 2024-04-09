<?php

declare(strict_types=1);

use App\Models\User;

test('to array', function () {
    $user = User::factory()->create()->fresh();

    expect(array_keys($user->toArray()))->toBe([
        'id',
        'name',
        'username',
        'bio',
        'email',
        'email_verified_at',
        'created_at',
        'updated_at',
        'links_sort',
        'settings',
        'avatar',
        'is_verified',
        'mail_preference_time',
        'github_username',
        'prefers_anonymous_questions',
        'is_company_verified',
        'avatar_updated_at',
    ]);
});

test('is verified', function () {
    $user = User::factory()->create([
        'is_verified' => true,
        'username' => 'test',
    ]);

    expect($user->is_verified)->toBeTrue();
});

test('is verified because in list of fixed sponsors', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'username' => 'test',
    ]);

    config()->set('sponsors.github_usernames', ['test']);

    expect($user->is_verified)->toBeTrue()
        ->and($user->is_company_verified)->toBeFalse();
});

test('is not verified because not in sponsors', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'username' => 'test',
    ]);

    config()->set('sponsors.github_usernames', ['test2']);

    expect($user->is_verified)->toBeFalse()
        ->and($user->is_company_verified)->toBeFalse();
});

test('is verified because in list of fixed company sponsors', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'username' => 'test',
    ]);

    config()->set('sponsors.github_company_usernames', ['test']);

    expect($user->is_verified)->toBeTrue()
        ->and($user->is_company_verified)->toBeTrue();
});
