<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use App\Notifications\UserFollowed;

final readonly class CreateFollow
{
    /**
     * Follow the target user.
     */
    public function handle(User $user, int $targetId): void
    {
        if ($user->id === $targetId) {
            return;
        }

        $changes = $user->following()->syncWithoutDetaching($targetId);

        if (in_array($targetId, $changes['attached'], true)) {
            $target = User::find($targetId);

            $target?->notify(new UserFollowed($user));
        }
    }
}
