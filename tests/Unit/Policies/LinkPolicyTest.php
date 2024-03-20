<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\User;

test('delete', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create(['user_id' => $user->id]);

    expect($user->can('delete', $link))->toBeTrue();

    $user = User::factory()->create();

    expect($user->can('delete', $link))->toBeFalse();
});
