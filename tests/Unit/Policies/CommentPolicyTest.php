<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\User;

test('create', function () {
    $user = User::factory()->create();

    expect($user->can('create', Comment::class))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('create', Comment::class))->toBeTrue();
});

test('update', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->for($user, 'owner')->create();

    expect($user->can('update', $comment))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('update', $comment))->toBeFalse();
});

test('delete', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->for($user, 'owner')->create();

    expect($user->can('delete', $comment))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('delete', $comment))->toBeFalse();
});
