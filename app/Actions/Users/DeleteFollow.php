<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

final readonly class DeleteFollow
{
    /**
     * Unfollow the target user.
     */
    public function handle(User $user, int $targetId): void
    {
        $user->following()->detach($targetId);
    }
}
