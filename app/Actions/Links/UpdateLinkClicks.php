<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\Link;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\IpUtils;

final readonly class UpdateLinkClicks
{
    /**
     * Increment the click count unless the visitor is the link owner
     * or has already clicked within the last day (deduplication).
     */
    public function handle(Link $link, string $ip, ?int $ownerId = null): bool
    {
        if ($ownerId === $link->user_id) {
            return false;
        }

        $cacheKey = IpUtils::anonymize($ip).'-clicked-'.$link->id;

        if (Cache::has($cacheKey)) {
            return false;
        }

        Link::query()
            ->whereKey($link->id)
            ->increment('click_count');

        Cache::put($cacheKey, true, now()->addDay());

        return true;
    }
}
