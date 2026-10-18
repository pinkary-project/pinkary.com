<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\UserResource;
use App\Queries\Users\DiscoverableUsersQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class UserDirectoryController
{
    /** List verified users for the directory. */
    public function __invoke(PaginatedRequest $request): AnonymousResourceCollection
    {
        $users = new DiscoverableUsersQuery($request->user()?->id)
            ->builder()
            ->whereNotNull('email_verified_at')
            ->orderByDesc('followed_by_me')
            ->orderByDesc('followers_count')
            ->orderBy('username')
            ->orderBy('id')
            ->simplePaginate($request->perPage());

        return UserResource::collection($users);
    }
}
