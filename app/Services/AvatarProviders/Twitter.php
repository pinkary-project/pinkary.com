<?php

declare(strict_types=1);

namespace App\Services\AvatarProviders;

use App\Contracts\Services\AvatarProvider;

final readonly class Twitter implements AvatarProvider
{
    /**
     * If the provider is applicable for the given link.
     */
    public function applicable(string $link): bool
    {
        return (str_contains($link, 'twitter.com/') || str_contains($link, 'x.com/'))
            && $this->getUsername($link) !== null;
    }

    /**
     * Get the avatar URL for the given link address.
     */
    public function getUrl(string $link): string
    {
        $username = $this->getUsername($link);

        foreach (['?', '#', '/'] as $char) {
            $username = str($username)->before($char)->value();
        }

        return 'https://unavatar.io/twitter/'.$username;
    }

    /**
     * Get the Twitter username from the link.
     */
    private function getUsername(string $link): ?string
    {
        preg_match('/twitter.com\/@?(.+)\/?/i', $link, $matches);

        if (! is_null((isset($matches[1]) && mb_strlen($matches[1]) > 3) ? $matches[1] : null)) {
            return (isset($matches[1]) && mb_strlen($matches[1]) > 3) ? $matches[1] : null;
        }

        preg_match('/x.com\/@?(.+)\/?/i', $link, $matches);

        return (isset($matches[1]) && mb_strlen($matches[1]) > 3) ? $matches[1] : null;
    }
}
