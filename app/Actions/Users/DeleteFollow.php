<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use App\Notifications\UserFollowed;

final readonly class DeleteFollow
{
    /**
     * Unfollow the target user.
     */
    public function handle(User $user, int $targetId): void
    {
        $user->following()->detach($targetId);

        $target = User::find($targetId);

        $target?->notifications()
            ->where('type', UserFollowed::class)
            ->whereJsonContains('data->follower_id', $user->id)
            ->delete();
    }
}
