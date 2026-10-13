<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Users\CreateFollow;
use App\Actions\Users\DeleteFollow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class UserFollowController
{
    /** Follow a user. */
    public function store(Request $request, User $user, CreateFollow $createFollow): JsonResponse
    {
        Gate::authorize('follow', $user);

        /** @var User $follower */
        $follower = $request->user();

        $createFollow->handle($follower, $user->id);

        return response()->json(['data' => [
            'followed' => true,
            'followers' => $user->followers()->count(),
        ]]);
    }

    /** Unfollow a user. */
    public function destroy(Request $request, User $user, DeleteFollow $deleteFollow): JsonResponse
    {
        Gate::authorize('unfollow', $user);

        /** @var User $follower */
        $follower = $request->user();

        $deleteFollow->handle($follower, $user->id);

        return response()->json(['data' => [
            'followed' => false,
            'followers' => $user->followers()->count(),
        ]]);
    }
}
