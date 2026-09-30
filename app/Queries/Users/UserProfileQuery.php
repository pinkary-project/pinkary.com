<?php

declare(strict_types=1);

namespace App\Queries\Users;

use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

final readonly class UserProfileQuery
{
    /**
     * Eager-load relations and counts required by the user profile card.
     *
     * @param  int|null  $viewerId  The authenticated viewer, if any.
     */
    public function load(User $user, ?int $viewerId = null): User
    {
        // Hidden links are filtered for everyone but the owner, who has to be
        // able to see and unhide their own.
        $user->loadMissing([
            'links' => fn (Relation $query) => $query->when(
                $viewerId !== $user->id,
                fn (Builder $q) => $q->where('is_visible', true),
            ),
        ]);
        $user->loadCount(['followers', 'following']);

        // Without these aliases UserResource falls back to a live EXISTS per
        // flag, so every signed-in profile read paid two extra queries.
        $user->loadExists([
            'followers as followed_by_me' => fn (Builder $query) => $query->when(
                $viewerId !== null,
                fn (Builder $q) => $q->where('follower_id', $viewerId),
                fn (Builder $q) => $q->whereRaw('1 = 0'),
            ),
            'following as follows_me' => fn (Builder $query) => $query->when(
                $viewerId !== null,
                fn (Builder $q) => $q->where('user_id', $viewerId),
                fn (Builder $q) => $q->whereRaw('1 = 0'),
            ),
        ]);

        $user->setAttribute(
            'posts_count',
            $user->questionsReceived()->tap(new WhereNotModerated)->where('answer', '!=', '')->count(),
        );

        return $user;
    }
}
