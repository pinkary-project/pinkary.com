<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Jobs\UpdateUserAvatar;
use App\Models\Link;
use App\Models\User;

final readonly class DeleteLink
{
    /**
     * Delete the link and refresh the avatar when needed.
     */
    public function handle(User $user, Link $link): void
    {
        $link->delete();

        if (! $user->is_uploaded_avatar) {
            UpdateUserAvatar::dispatchFor($user);
        }
    }
}
