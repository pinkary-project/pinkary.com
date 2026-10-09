<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Repost;
use App\Models\User;

final readonly class RepostPolicy
{
    /** Determine whether the user can delete the repost. */
    public function delete(User $user, Repost $repost): bool
    {
        return $user->id === $repost->user_id;
    }
}
