<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final readonly class Avatar
{
    /**
     * Create a new avatar for the given name and email address.
     */
    public function __construct(
        private User $user,
    ) {
        //
    }

    /**
     * Get the avatar URL.
     */
    public function url(string $service = 'gravatar'): string
    {
        if ($service === 'github' && $this->user->github_username) {
            return "https://avatars.githubusercontent.com/{$this->user->github_username}";
        }

        if ($service === 'gravatar') {
            $gravatarHash = hash('sha256', mb_strtolower($this->user->email));
            $gravatarUrl = "https://gravatar.com/avatar/{$gravatarHash}?s=300&d=404";
            $headers = get_headers($gravatarUrl);
            if ($headers !== false && ! in_array('HTTP/1.1 404 Not Found', $headers, true)) {
                return $gravatarUrl;
            }
        }

        return asset('img/default-avatar.png');
    }
}
