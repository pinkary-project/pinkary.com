<?php

declare(strict_types=1);

namespace App\Services\DynamicAutocomplete\Types;

use App\Contracts\Services\DynamicAutocompleteResult;
use App\Models\User;
use App\Services\DynamicAutocomplete\Results\Collection;
use App\Services\DynamicAutocomplete\Results\MentionResult;
use Illuminate\Database\Eloquent\Builder;

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
     * @return Collection<int, DynamicAutocompleteResult>
     */
    public function search(?string $query): Collection
    {
        return Collection::make(
            User::query()
                ->when(auth()->id(), fn (Builder $constraint, string|int $id) => $constraint->whereKeyNot($id))
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
                ->map(fn (User $user): DynamicAutocompleteResult => new MentionResult(
                    id: $user->id,
                    name: $user->name,
                    username: "@{$user->username}",
                    avatar_src: $user->avatar_url,
                    replacement: "@{$user->username}",
                    is_followed_by_user: $user->is_followed_by_user, // @phpstan-ignore-line
                    is_verified: $user->is_verified,
                    is_company_verified: $user->is_company_verified,
                )
                ));
    }
}
