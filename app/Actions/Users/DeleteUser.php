<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

final readonly class DeleteUser
{
    /**
     * Permanently delete the user and related data.
     */
    public function handle(User $user): void
    {
        $user->purge();
    }
}
