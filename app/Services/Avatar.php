<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\AvatarProvider;
use App\Services\AvatarProviders\GitHub;
use App\Services\AvatarProviders\Twitter;

final readonly class Avatar
{
    /**
     * Create a new avatar for the given name and email address.
     *
     * @param string $email
     * @param ?string $githubUsername
     */
    public function __construct(
        private string  $email,
        private ?string $githubUsername = null,
    ) {
        //
    }

    /**
     * Get the avatar URL.
     */
    public function url(): string
    {

        $fallbacks = collect();

        if ($this->githubUsername) {
            $fallbacks->add("https://avatars.githubusercontent.com/$this->githubUsername");
        }

        $gravatarHash = hash('sha256', mb_strtolower($this->email));
        $fallbacks->add("https://gravatar.com/avatar/$gravatarHash?s=300");

        /* @var string $resolved */
        $resolved = $fallbacks->first();

        return $resolved;
    }

}
