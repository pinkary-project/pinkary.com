<?php

declare(strict_types=1);

namespace App\Queries\Channels;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class ChannelsQuery
{
    /**
     * List popular channels, or search them by name (admin-only hidden from non-admins).
     *
     * @return Collection<int, Channel>
     */
    public function get(string $query = '', bool $isAdmin = false, int $limit = 8): Collection
    {
        $q = mb_trim($query);

        if ($q === '') {
            /** @var Collection<int, Channel> $channels */
            $channels = Cache::remember(
                'channels:popular',
                3600,
                fn (): Collection => Channel::query()
                    ->orderByDesc('questions_count')
                    ->orderBy('name')
                    ->limit($limit)
                    ->get(),
            );

            if ($isAdmin) {
                return $channels;
            }

            return $channels->reject(fn (Channel $channel): bool => $channel->isAdminOnly())->values();
        }

        return Channel::query()
            ->when(! $isAdmin, fn (Builder $builder) => $builder->whereNotIn('slug', Channel::ADMIN_ONLY_SLUGS))
            ->where('name', 'like', "%{$q}%")
            ->orderByDesc('questions_count')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
