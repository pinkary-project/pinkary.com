<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\UserFollowings;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class IndexController
{
    public function __invoke(PaginatedRequest $request, User $user): AnonymousResourceCollection
    {
        $viewerId = $request->user()?->id;

        $following = $user->following()
            ->withExists([
                'followers as followed_by_me' => fn ($query) => $query->when(
                    $viewerId,
                    fn ($q) => $q->where('follower_id', $viewerId),
                    fn ($q) => $q->whereRaw('1 = 0')
                ),
            ])
            ->simplePaginate((int) ($request->validated()['per_page'] ?? 20));

        return UserResource::collection($following);
    }
}
