<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final readonly class PeopleToFollowRecommendations
{
    private const int DEFAULT_LIMIT = 5;

    private const int VERIFIED_LIMIT = 2;

    private const int FAMOUS_LIMIT = 50;

    private const int EXTENDED_FAMOUS_LIMIT = 200;

    private const int THREAD_DEPTH_CAP = 20;

    /**
     * @return EloquentCollection<int, User>
     */
    public function forContext(
        ?int $authenticatedUserId,
        string $context = 'generic',
        ?int $contextUserId = null,
        ?string $contextQuestionId = null,
        int $limit = self::DEFAULT_LIMIT,
    ): EloquentCollection {
        /** @var array<int, int> $selectedIds */
        $selectedIds = match ($context) {
            'profile' => $contextUserId === null
                ? []
                : $this->latestInteractedUserIds(
                    userId: $contextUserId,
                    authenticatedUserId: $authenticatedUserId,
                    excludeIds: [$contextUserId],
                    limit: $limit,
                ),
            'question' => $contextQuestionId === null
                ? []
                : $this->questionContextUserIds($contextQuestionId, $authenticatedUserId, $limit),
            default => [],
        };

        if (count($selectedIds) < $limit) {
            $selectedIds = [
                ...$selectedIds,
                ...$this->genericFallbackUserIds(
                    authenticatedUserId: $authenticatedUserId,
                    excludeIds: $selectedIds,
                    limit: $limit - count($selectedIds),
                ),
            ];
        }

        $users = $this->usersForIds(array_slice($selectedIds, 0, $limit));

        if ($users->count() < $limit) {
            $topUpIds = $this->genericFallbackUserIds(
                authenticatedUserId: $authenticatedUserId,
                excludeIds: $selectedIds,
                limit: $limit - $users->count(),
            );

            if ($topUpIds !== []) {
                $users = $users->merge($this->usersForIds($topUpIds)); // @codeCoverageIgnore
            }
        }

        if ($authenticatedUserId !== null) {
            $users->loadExists([ // @phpstan-ignore-line
                'following as is_follower' => function (Builder $query) use ($authenticatedUserId): void {
                    $query->where('user_id', $authenticatedUserId);
                },
                'followers as is_following' => function (Builder $query) use ($authenticatedUserId): void {
                    $query->where('follower_id', $authenticatedUserId);
                },
            ]);
        }

        return $users;
    }

    /**
     * @return array<int, int>
     */
    private function questionContextUserIds(string $questionId, ?int $authenticatedUserId, int $limit): array
    {
        $question = Question::query()
            ->select('id', 'from_id', 'to_id', 'parent_id')
            ->find($questionId);

        if ($question === null) {
            return [];
        }

        $selectedIds = $this->availableUserIds([$question->to_id], $authenticatedUserId, [], 1);

        // Load the full ancestor chain in a single recursive CTE (depth-capped at THREAD_DEPTH_CAP).
        /**
         * @var array<array-key, object{id: int, from_id: int, to_id: int, parent_id: int, depth: int}>
         */
        $threadRows = DB::select(
            'WITH RECURSIVE thread (id, from_id, to_id, parent_id, depth) AS (
                SELECT id, from_id, to_id, parent_id, 0 FROM questions WHERE id = ?
                UNION ALL
                SELECT q.id, q.from_id, q.to_id, q.parent_id, t.depth + 1
                FROM questions q INNER JOIN thread t ON q.id = t.parent_id
                WHERE t.depth < ?
            )
            SELECT from_id, to_id FROM thread',
            [$questionId, self::THREAD_DEPTH_CAP]
        );

        $threadParticipantIds = [];
        foreach ($threadRows as $row) {
            $threadParticipantIds[] = (int) $row->from_id;
            $threadParticipantIds[] = (int) $row->to_id;
        }

        if (count($selectedIds) < $limit) {
            $selectedIds = [
                ...$selectedIds,
                ...$this->availableUserIds(
                    userIds: $threadParticipantIds,
                    authenticatedUserId: $authenticatedUserId,
                    excludeIds: $selectedIds,
                    limit: $limit - count($selectedIds),
                ),
            ];
        }

        if (count($selectedIds) < $limit) {
            $selectedIds = [
                ...$selectedIds,
                ...$this->latestInteractedUserIds(
                    userId: $question->to_id,
                    authenticatedUserId: $authenticatedUserId,
                    excludeIds: $selectedIds,
                    limit: $limit - count($selectedIds),
                ),
            ];
        }

        return array_slice($selectedIds, 0, $limit);
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return array<int, int>
     */
    private function latestInteractedUserIds(
        int $userId,
        ?int $authenticatedUserId,
        array $excludeIds,
        int $limit,
    ): array {
        if ($limit <= 0) {
            return [];
        }

        $interactions = Question::query()
            ->selectRaw('CASE WHEN from_id = ? THEN to_id ELSE from_id END as counterpart_id', [$userId])
            ->addSelect('updated_at')
            ->where(function (Builder $query) use ($userId): void {
                $query->where('from_id', $userId)
                    ->orWhere('to_id', $userId);
            })
            ->whereNotNull('answer')
            ->where('is_ignored', false)
            ->where('is_reported', false);

        /** @var array<int, int> $candidateIds */
        $candidateIds = DB::query()
            ->fromSub($interactions, 'interactions')
            ->select('counterpart_id')
            ->selectRaw('MAX(updated_at) as latest_interaction_at')
            ->groupBy('counterpart_id')
            ->orderByDesc('latest_interaction_at')
            ->limit(max(self::FAMOUS_LIMIT, $limit * 5))
            ->pluck('counterpart_id')
            ->all();

        return $this->availableUserIds(
            userIds: $candidateIds,
            authenticatedUserId: $authenticatedUserId,
            excludeIds: array_values(array_unique([...$excludeIds, $userId])),
            limit: $limit,
        );
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return array<int, int>
     */
    private function genericFallbackUserIds(?int $authenticatedUserId, array $excludeIds, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        $selectedIds = $this->verifiedUserIds(
            authenticatedUserId: $authenticatedUserId,
            excludeIds: $excludeIds,
            limit: min(self::VERIFIED_LIMIT, $limit),
        );

        if (count($selectedIds) < $limit) {
            $selectedIds = [
                ...$selectedIds,
                ...$this->famousUserIds(
                    authenticatedUserId: $authenticatedUserId,
                    excludeIds: [...$excludeIds, ...$selectedIds],
                    limit: $limit - count($selectedIds),
                    poolLimit: self::FAMOUS_LIMIT,
                ),
            ];
        }

        if (count($selectedIds) < $limit) {
            $selectedIds = [
                ...$selectedIds,
                ...$this->famousUserIds(
                    authenticatedUserId: $authenticatedUserId,
                    excludeIds: [...$excludeIds, ...$selectedIds],
                    limit: $limit - count($selectedIds),
                    poolLimit: self::EXTENDED_FAMOUS_LIMIT,
                ),
            ];
        }

        return array_slice($selectedIds, 0, $limit);
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return array<int, int>
     */
    private function verifiedUserIds(?int $authenticatedUserId, array $excludeIds, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        /** @var array<int, int> $verifiedUserIds */
        $verifiedUserIds = $this->availableUsersQuery($authenticatedUserId, $excludeIds)
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
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id')
            ->all();

        return array_values($verifiedUserIds);
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return array<int, int>
     */
    private function famousUserIds(
        ?int $authenticatedUserId,
        array $excludeIds,
        int $limit,
        int $poolLimit,
    ): array {
        if ($limit <= 0) {
            return [];
        }

        /** @var array<int, int> $famousUsers */
        $famousUsers = Cache::remember(
            "top-{$poolLimit}-users",
            now()->endOfDay(),
            fn (): array => $this->famousUsersQuery()->limit($poolLimit)->pluck('id')->all()
        );

        $famousUsers = collect($famousUsers)
            ->sortBy(static fn (): int => random_int(PHP_INT_MIN, PHP_INT_MAX))
            ->values()
            ->all();

        return $this->availableUserIds(
            userIds: $famousUsers,
            authenticatedUserId: $authenticatedUserId,
            excludeIds: $excludeIds,
            limit: $limit,
        );
    }

    /**
     * @return Builder<User>
     */
    private function famousUsersQuery(): Builder
    {
        return User::query()
            ->whereHas('links', function (Builder $query): void {
                $query->where('url', 'like', '%twitter.com%')
                    ->orWhere('url', 'like', '%github.com%')
                    ->orWhere('url', 'like', '%://x.com%');
            })
            ->withCount(['questionsReceived as answered_questions_count' => function (Builder $query): void {
                $query->whereNotNull('answer');
            }])
            ->orderByDesc('answered_questions_count');
    }

    /**
     * @param  array<int, int>  $userIds
     * @return EloquentCollection<int, User>
     */
    private function usersForIds(array $userIds): EloquentCollection
    {
        if ($userIds === []) {
            return new EloquentCollection();
        }

        /** @var EloquentCollection<int, User> $users */
        $users = User::query()
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        return new EloquentCollection(
            array_values(array_filter(
                array_map(static fn (int $userId): ?User => $users->get($userId), $userIds),
                static fn (?User $user): bool => $user instanceof User,
            ))
        );
    }

    /**
     * @param  array<int, int>  $userIds
     * @param  array<int, int>  $excludeIds
     * @return array<int, int>
     */
    private function availableUserIds(
        array $userIds,
        ?int $authenticatedUserId,
        array $excludeIds,
        int $limit,
    ): array {
        if ($limit <= 0) {
            return [];
        }

        $orderedUserIds = array_values(array_unique(array_filter(
            $userIds,
            static fn (int $userId): bool => $userId > 0,
        )));

        if ($orderedUserIds === []) {
            return [];
        }

        /** @var array<int, int> $availableIds */
        $availableIds = $this->availableUsersQuery($authenticatedUserId, $excludeIds)
            ->whereIn('id', $orderedUserIds)
            ->pluck('id')
            ->all();

        $availableLookup = array_flip($availableIds);

        return array_slice(array_filter(
            $orderedUserIds,
            static fn (int $userId): bool => array_key_exists($userId, $availableLookup),
        ), 0, $limit);
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return Builder<User>
     */
    private function availableUsersQuery(?int $authenticatedUserId, array $excludeIds): Builder
    {
        return User::query()
            ->when($excludeIds !== [], function (Builder $query) use ($excludeIds): void {
                $query->whereNotIn('id', array_values(array_unique($excludeIds)));
            })
            ->when($authenticatedUserId !== null, function (Builder $query) use ($authenticatedUserId): void {
                $query->where('id', '!=', $authenticatedUserId)
                    ->whereDoesntHave('followers', function (Builder $query) use ($authenticatedUserId): void {
                        $query->whereKey($authenticatedUserId);
                    });
            });
    }
}
