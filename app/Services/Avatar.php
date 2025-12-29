<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

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
            $response = Http::head($gravatarUrl);
            if (! $response->notFound()) {
                return $gravatarUrl;
            }
        }

        return asset('img/default-avatar.png');
    }
}
