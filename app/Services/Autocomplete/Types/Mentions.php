<?php

declare(strict_types=1);

namespace App\Services\Autocomplete\Types;

use App\Models\User;
use App\Services\Autocomplete\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class Mentions extends Type
{
    /**
     * The delimiter for the autocompletion type.
     */
    public function delimiter(): string
    {
        return '@';
    }

    /**
     * The regular expression that represents a matching
     * string after the delimiter.
     */
    public function matchExpression(): string
    {
        return '[^\s,.?!\/@<]+';
    }

    /**
     * Perform a search using the query to return
     * autocompletion options.
     *
     * @return Collection<int, Result>
     */
    public function search(?string $query): Collection
    {
        // @phpstan-ignore-next-line
        return Collection::make(
            User::query()
                ->when(auth()->id(), fn (Builder $constraint, string|int $id) => $constraint->whereKeyNot($id))
                ->whereNotNull('email_verified_at')
                ->where(fn (Builder $groupedConstraint) => $groupedConstraint
                    ->where('name', 'like', "{$query}%")
                    ->orWhere('username', 'like', "{$query}%")
                )
                ->withCount('followers')
                ->withExists([
                    'followers as is_followed_by_user' => fn (Builder $follower): Builder => $follower
                        ->where('follower_id', '=', auth()->id()),
                ])
                ->orderByDesc('is_followed_by_user')
                ->orderByDesc('followers_count')
                ->orderBy('username')
                ->limit(10)
                ->get()
                ->map(fn (User $user): Result => new Result(
                    id: $user->id,
                    replacement: "@{$user->username}",
                    view: 'components.autocomplete.mention-item',
                    payload: [
                        'name' => $user->name,
                        'username' => $user->username,
                        'avatarSrc' => $user->avatar_url,
                        'isFollowedByUser' => $user->is_followed_by_user, // @phpstan-ignore-line
                        'isVerified' => $user->is_verified,
                        'isCompanyVerified' => $user->is_company_verified,
                    ],
                ))
        );
    }
}
