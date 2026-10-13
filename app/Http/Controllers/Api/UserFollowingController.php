<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class UserFollowingController
{
    /** List who a user follows. */
    public function index(PaginatedRequest $request, User $user): AnonymousResourceCollection
    {
        $viewerId = $request->user()?->id;

        $following = $user->following()
            ->withExists([
                'followers as followed_by_me' => fn (Builder $query) => $query->when(
                    $viewerId,
                    fn (Builder $q) => $q->where('follower_id', $viewerId),
                    fn (Builder $q) => $q->whereRaw('1 = 0')
                ),
                'following as follows_me' => fn (Builder $query) => $query->when(
                    $viewerId,
                    fn (Builder $q) => $q->where('user_id', $viewerId),
                    fn (Builder $q) => $q->whereRaw('1 = 0')
                ),
            ])
            // Both relationship directions use the followers pivot table.
            ->latest('followers.id')
            ->simplePaginate($request->perPage());

        return UserResource::collection($following);
    }
}
