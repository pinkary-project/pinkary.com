<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\Link;

final readonly class UpdateLinkVisibility
{
    /**
     * Toggle whether the link is visible.
     */
    public function handle(Link $link): void
    {
        $link->update([
            'is_visible' => ! $link->is_visible,
        ]);
    }
}
