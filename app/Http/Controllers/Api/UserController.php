<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\UserResource;
use App\Jobs\IncrementViews;
use App\Models\Question;
use App\Models\User;
use App\Queries\Users\UserProfileQuery;
use App\Services\PeopleToFollowRecommendations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final readonly class UserController
{
    /** Return a user's public profile. */
    public function show(Request $request, User $user, UserProfileQuery $userProfileQuery): UserResource
    {
        // Sessionless guest requests cannot use session-based view deduplication.
        if ($request->user() instanceof User) {
            IncrementViews::dispatchUsingSession($user);
        }

        return new UserResource($userProfileQuery->load($user, $request->user()?->id));
    }

    /** List verified users for the directory. */
    public function directory(PaginatedRequest $request): AnonymousResourceCollection
    {
        $users = $this->discoveryQuery($request->user()?->id)
            ->whereNotNull('email_verified_at')
            ->orderByDesc('followed_by_me')
            ->orderByDesc('followers_count')
            ->orderBy('username')
            ->orderBy('id')
            ->simplePaginate($request->perPage());

        return UserResource::collection($users);
    }

    /** Return the existing contextual people-to-follow ranking in pages. */
    public function recommendations(Request $request, PeopleToFollowRecommendations $recommendations): AnonymousResourceCollection
    {
        /**
         * @var array{
         *     context?: 'generic'|'profile'|'question',
         *     context_user_id?: int|numeric-string,
         *     context_question_id?: string,
         *     per_page?: int|numeric-string,
         *     page?: int|numeric-string
         * } $validated
         */
        $validated = $request->validate([
            'context' => ['sometimes', 'string', Rule::in(['generic', 'profile', 'question'])],
            'context_user_id' => ['required_if:context,profile', 'prohibited_unless:context,profile', 'integer', 'min:1', 'exists:users,id'],
            'context_question_id' => ['required_if:context,question', 'prohibited_unless:context,question', 'uuid', 'exists:questions,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $context = $validated['context'] ?? 'generic';
        $contextUserId = isset($validated['context_user_id']) ? (int) $validated['context_user_id'] : null;
        $contextQuestionId = $validated['context_question_id'] ?? null;
        $viewerId = $request->user()?->id;

        if (is_string($contextQuestionId)) {
            $question = Question::query()->findOrFail($contextQuestionId);

            Gate::authorize('view', $question);
        }

        $cacheKey = 'api:people-to-follow:'.hash('sha256', implode('|', [
            $viewerId ?? 'guest',
            $context,
            $contextUserId ?? '',
            $contextQuestionId ?? '',
        ]));

        /** @var list<int> $recommendedIds */
        $recommendedIds = cache()->remember(
            $cacheKey,
            now()->addMinutes(10),
            fn (): array => $recommendations->forContext(
                authenticatedUserId: $viewerId,
                context: $context,
                contextUserId: $contextUserId,
                contextQuestionId: $contextQuestionId,
                limit: 200,
            )->modelKeys(),
        );

        /** @var list<int> $eligibleIds */
        $eligibleIds = User::query()
            ->whereKey($recommendedIds)
            ->whereNotNull('email_verified_at')
            ->when($viewerId !== null, function (Builder $query) use ($viewerId): void {
                $query->whereKeyNot($viewerId)
                    ->whereDoesntHave('followers', fn (Builder $followers) => $followers->where('follower_id', $viewerId));
            })
            ->pluck('id')
            ->all();

        $eligibleLookup = array_flip($eligibleIds);
        $orderedIds = array_values(array_filter(
            $recommendedIds,
            static fn (int $userId): bool => isset($eligibleLookup[$userId]),
        ));

        $perPage = (int) ($validated['per_page'] ?? 5);
        $page = (int) ($validated['page'] ?? 1);
        $pageIds = array_slice($orderedIds, ($page - 1) * $perPage, $perPage);
        $users = $this->discoveryQuery($viewerId)->whereKey($pageIds)->get()->keyBy('id');
        $items = collect($pageIds)
            ->map(fn (int $userId): ?User => $users->get($userId))
            ->filter()
            ->values();

        $paginator = new LengthAwarePaginator(
            $items,
            count($orderedIds),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return UserResource::collection($paginator);
    }

    /** @return Builder<User> */
    private function discoveryQuery(?int $viewerId): Builder
    {
        return User::query()
            ->when($viewerId !== null, fn (Builder $query) => $query->whereKeyNot($viewerId))
            ->withCount(['followers', 'following'])
            ->withExists([
                'followers as followed_by_me' => fn (Builder $query) => $query->when(
                    $viewerId,
                    fn (Builder $builder) => $builder->where('follower_id', $viewerId),
                    fn (Builder $builder) => $builder->whereRaw('1 = 0'),
                ),
                'following as follows_me' => fn (Builder $query) => $query->when(
                    $viewerId,
                    fn (Builder $builder) => $builder->where('user_id', $viewerId),
                    fn (Builder $builder) => $builder->whereRaw('1 = 0'),
                ),
            ]);
    }
}
