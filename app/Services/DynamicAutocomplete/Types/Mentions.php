<?php

declare(strict_types=1);

namespace App\Services\DynamicAutocomplete\Types;

use App\Contracts\Services\DynamicAutocompleteResult;
use App\Models\User;
use App\Services\DynamicAutocomplete\Results\Collection;
use App\Services\DynamicAutocomplete\Results\MentionResult;
use Illuminate\Database\Eloquent\Builder;

final class Mentions extends Type
{
    public function delimiter(): string
    {
        return '@';
    }

    public function matchExpression(): string
    {
        return '[^\s,.?!\/@<]+';
    }

    public function search(?string $query): Collection
    {
        return Collection::make(
            User::query()
                ->when(auth()->id(), fn (Builder $constraint, $id) => $constraint->whereKeyNot($id))
                ->where(fn (Builder $groupedConstraint) => $groupedConstraint
                    ->where('name', 'like', "{$query}%")
                    ->orWhere('username', 'like', "{$query}%")
                )
                ->withCount('followers')
                ->withExists(['followers as is_followed_by_user' => function (Builder $follower) {
                    return $follower->where('follower_id', '=', auth()->id());
                }])
                ->orderByDesc('is_followed_by_user')
                ->orderByDesc('followers_count')
                ->orderBy('username')
                ->limit(10)
                ->get()
                ->map(function (User $user): DynamicAutocompleteResult {
                    return new MentionResult(
                        id: $user->id,
                        name: $user->name,
                        username: "@{$user->username}",
                        avatar_src: $user->avatar_url,
                        replacement: "@{$user->username}",
                        is_followed_by_user: $user->is_followed_by_user, // @phpstan-ignore-line
                        is_verified: $user->is_verified,
                        is_company_verified: $user->is_company_verified,
                    );
                }));
    }
}
