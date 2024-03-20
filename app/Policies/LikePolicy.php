<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Like;
use App\Models\User;

final readonly class LikePolicy
{
    /**
     * Determine whether the user can delete the like.
     */
    public function delete(User $user, Like $like): bool
    {
        return $user->id === $like->user_id;
    }
}
