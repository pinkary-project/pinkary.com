<?php

declare(strict_types=1);

namespace App\Services;

final readonly class Avatar
{
    /**
     * Create a new avatar for the given name and email address.
     */
    public function __construct(
        private string $email,
    ) {
        //
    }

    /**
     * Get the avatar URL.
     */
    public function url(): string
    {
        $gravatarHash = hash('sha256', mb_strtolower($this->email));

        return "https://gravatar.com/avatar/$gravatarHash?s=300";
    }
}
