<?php

declare(strict_types=1);

namespace App\Queries\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class DiscoverableUsersQuery
{
    public function __construct(private ?int $viewerId) {}

    /** @return Builder<User> */
    public function builder(): Builder
    {
        return User::query()
            ->when($this->viewerId !== null, fn (Builder $query) => $query->whereKeyNot($this->viewerId))
            ->withCount(['followers', 'following'])
            ->withExists([
                'followers as followed_by_me' => fn (Builder $query) => $query->when(
                    $this->viewerId,
                    fn (Builder $builder) => $builder->where('follower_id', $this->viewerId),
                    fn (Builder $builder) => $builder->whereRaw('1 = 0'),
                ),
                'following as follows_me' => fn (Builder $query) => $query->when(
                    $this->viewerId,
                    fn (Builder $builder) => $builder->where('user_id', $this->viewerId),
                    fn (Builder $builder) => $builder->whereRaw('1 = 0'),
                ),
            ]);
    }
}
