<?php

declare(strict_types=1);

use App\Models\User;

test('follow', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    expect($user->can('follow', $target))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('follow', $user))->toBeFalse();
});

test('unfollow', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    expect($user->can('unfollow', $target))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('unfollow', $user))->toBeFalse();
});
