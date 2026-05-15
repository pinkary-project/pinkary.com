<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class PeopleToFollow
{
    /**
     * Create a new people-to-follow query.
     */
    public function __construct(
        private ?User $viewer = null,
    ) {}

    /**
     * Get the discovery users for people-to-follow surfaces.
     *
     * @return Collection<int, User>
     */
    public function get(int $limit = 10): Collection
    {
        $verifiedUsers = $this->verifiedUsers(min(2, $limit));

        return $this->famousUsers($verifiedUsers, $limit)
            ->merge($verifiedUsers)
            ->shuffle()
            ->load('links');
    }

    /**
     * Get the users with the most answered questions.
     *
     * @param  Collection<int, User>  $except
     * @return Collection<int, User>
     */
    private function famousUsers(Collection $except, int $limit): Collection
    {
        $viewerId = $this->viewer?->id;

        $famousUsers = Cache::remember('top-50-users', now()->endOfDay(), fn (): array => User::query()
            ->whereHas('links', function (Builder $query): void {
                $query->where('url', 'like', '%twitter.com%')
                    ->orWhere('url', 'like', '%github.com%')
                    ->orWhere('url', 'like', '%://x.com%');
            })
            ->when($viewerId !== null, fn (Builder $query) => $query->whereKeyNot($viewerId))
            ->whereNotIn('id', $except->pluck('id'))
            ->withCount(['questionsReceived as answered_questions_count' => function (Builder $query): void {
                $query->whereNotNull('answer');
            }])
            ->orderBy('answered_questions_count', 'desc')
            ->limit(50)
            ->pluck('id')
            ->toArray());

        return User::query()
            ->whereIn('id', $famousUsers)
            ->when($viewerId !== null, function (Builder $query) use ($viewerId): void {
                $query->withExists([
                    'following as is_follower' => function (Builder $query) use ($viewerId): void {
                        $query->where('user_id', $viewerId);
                    },
                    'followers as is_following' => function (Builder $query) use ($viewerId): void {
                        $query->where('follower_id', $viewerId);
                    },
                ]);
            })
            ->inRandomOrder()
            ->limit(max(0, $limit - $except->count()))
            ->get();
    }

    /**
     * Get the verified discovery users.
     *
     * @return Collection<int, User>
     */
    private function verifiedUsers(int $limit): Collection
    {
        $viewerId = $this->viewer?->id;

        return User::query()
            ->whereHas('links', function (Builder $query): void {
                $query->where('url', 'like', '%twitter.com%')
                    ->orWhere('url', 'like', '%github.com%')
                    ->orWhere('url', 'like', '%://x.com%');
            })
            ->where(function (Builder $query): void {
                $query->where('is_verified', true)
                    ->orWhereIn('username', array_merge(
                        config()->array('sponsors.github_company_usernames', []),
                        config()->array('sponsors.github_usernames', [])
                    ));
            })
            ->when($viewerId !== null, fn (Builder $query) => $query->whereKeyNot($viewerId))
            ->when($viewerId !== null, function (Builder $query) use ($viewerId): void {
                $query->withExists([
                    'following as is_follower' => function (Builder $query) use ($viewerId): void {
                        $query->where('user_id', $viewerId);
                    },
                    'followers as is_following' => function (Builder $query) use ($viewerId): void {
                        $query->where('follower_id', $viewerId);
                    },
                ]);
            })
            ->limit($limit)
            ->inRandomOrder()
            ->get();
    }
}
