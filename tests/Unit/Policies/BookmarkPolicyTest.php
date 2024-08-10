<?php

declare(strict_types=1);

use App\Models\Bookmark;
use App\Models\User;

test('delete', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

    expect($user->can('delete', $bookmark))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('delete', $bookmark))->toBeFalse();
});
