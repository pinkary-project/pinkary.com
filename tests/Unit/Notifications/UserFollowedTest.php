<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\UserFollowed;

test('to database', function (): void {
    $follower = User::factory()->create();
    $target = User::factory()->create();

    $notification = new UserFollowed($follower);

    expect($notification->via($target))->toBe(['database'])
        ->and($notification->toDatabase($target))->toBe(['follower_id' => $follower->id]);
});
