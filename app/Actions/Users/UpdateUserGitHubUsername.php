<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

final readonly class UpdateUserGitHubUsername
{
    /**
     * Set or clear the user's GitHub username.
     */
    public function handle(User $user, ?string $githubUsername): void
    {
        $user->update([
            'github_username' => $githubUsername,
        ]);
    }
}
