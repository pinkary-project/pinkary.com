<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\Link;
use Illuminate\Support\Facades\Cache;

final readonly class UpdateLinkClicks
{
    /**
     * Increment the click count and remember this visitor for a day.
     */
    public function handle(int $linkId, string $cacheKey): void
    {
        Link::query()
            ->whereKey($linkId)
            ->increment('click_count');

        Cache::put($cacheKey, true, now()->addDay());
    }
}
