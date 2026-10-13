<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\Link;

final readonly class UpdateLink
{
    /**
     * Update the link, resetting click count when the URL changes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Link $link, array $attributes): void
    {
        if ($link->url !== $attributes['url']) {
            $attributes['click_count'] = 0;
        }

        $link->update($attributes);
    }
}
