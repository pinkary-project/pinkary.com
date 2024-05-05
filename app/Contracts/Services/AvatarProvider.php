<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface AvatarProvider
{
    /**
     * Check if the provider is applicable for the given link.
     */
    public function applicable(string $link): bool;

    /**
     * Get the avatar URL for the given link.
     */
    public function getUrl(string $link): string;
}
