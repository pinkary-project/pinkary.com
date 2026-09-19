<?php

declare(strict_types=1);

namespace App\Actions\Channels;

use App\Models\Channel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class GetChannels
{
    /**
     * List popular channels, or search them by name — mirroring the
     * web composer's channel picker (admin-only hidden from non-admins).
     *
     * @return Collection<int, Channel>
     */
    public function handle(string $query, bool $isAdmin): Collection
    {
        $channels = $query === ''
            ? Cache::remember(
                'channels:popular',
                3600,
                fn () => Channel::query()
                    ->orderByDesc('questions_count')
                    ->orderBy('name')
                    ->limit(8)
                    ->get(),
            )
            : Channel::query()
                ->when(! $isAdmin, fn ($builder) => $builder->whereNotIn('slug', Channel::ADMIN_ONLY_SLUGS))
                ->where('name', 'like', "%{$query}%")
                ->orderByDesc('questions_count')
                ->orderBy('name')
                ->limit(8)
                ->get();

        if (! $isAdmin) {
            $channels = $channels->reject(fn (Channel $channel): bool => $channel->isAdminOnly())->values();
        }

        return $channels;
    }
}
