<?php

declare(strict_types=1);

use App\Models\Like;
use App\Models\User;

test('delete', function () {
    $user = User::factory()->create();
    $like = Like::factory()->create(['user_id' => $user->id]);

    expect($user->can('delete', $like))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('delete', $like))->toBeFalse();
});
