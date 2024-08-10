<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Link;
use App\Models\User;

final readonly class LinkPolicy
{
    /**
     * Determine whether the user can delete the link.
     */
    public function delete(User $user, Link $link): bool
    {
        return $user->id === $link->user_id;
    }

    /**
     * Determine whether the user can update the link.
     */
    public function update(User $user, Link $link): bool
    {
        return $user->id === $link->user_id;
    }
}
