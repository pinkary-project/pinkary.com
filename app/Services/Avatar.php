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
     * @param  array<int, string>  $links
     */
    public function __construct(
        private string $email,
        private array $links,
    ) {
        //
    }

    /**
     * Get the avatar URL.
     */
    public function url(): string
    {
        $url = "https://unavatar.io/$this->email";

        $providers = [
            Twitter::class,
            GitHub::class,
        ];

        $fallbacks = collect();

        $gravatarHash = hash('sha256', mb_strtolower($this->email));

        foreach ($this->links as $link) {
            foreach ($providers as $provider) {
                $provider = type(new $provider())->as(AvatarProvider::class);

                if ($provider->applicable($link)) {
                    $fallback = $provider->getUrl($link);

                    if ($provider instanceof Twitter) {
                        $fallbacks->add($url);

                        $url = $fallback;
                    } else {
                        $fallbacks->add($fallback);
                    }
                }
            }
        }

        $fallbacks->add("https://gravatar.com/avatar/$gravatarHash?s=300");

        $fallbacks = $fallbacks->unique();

        return $url.$fallbacks
            ->map(fn (string $url): string => "?fallback=$url")
            ->implode('');
    }
}
