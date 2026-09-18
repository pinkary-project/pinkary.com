<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Users\CreateFollow;
use App\Actions\Users\DeleteFollow;
use App\Actions\Users\LoadProfile;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

final readonly class UserController
{
    /**
     * Show any user's public profile card, mirroring the web profile page.
     */
    public function show(Request $request, User $user, LoadProfile $loadProfile): JsonResource
    {
        return new UserResource($loadProfile->handle($user));
    }

    /**
     * Follow the user (idempotent, same policy as the web button).
     */
    public function follow(Request $request, User $user, CreateFollow $createFollow): JsonResponse
    {
        Gate::authorize('follow', $user);

        $me = $request->user();

        $followed = $user->followers()->where('follower_id', $me->id)->exists();

        if (! $followed) {
            $createFollow->handle($me, $user->id);
        }

        return response()->json(['data' => [
            'followed' => true,
            'followers' => $user->followers()->count(),
        ]]);
    }

    /**
     * Unfollow the user (idempotent).
     */
    public function unfollow(Request $request, User $user, DeleteFollow $deleteFollow): JsonResponse
    {
        Gate::authorize('unfollow', $user);

        $me = $request->user();

        if ($user->followers()->where('follower_id', $me->id)->exists()) {
            $deleteFollow->handle($me, $user->id);
        }

        return response()->json(['data' => [
            'followed' => false,
            'followers' => $user->followers()->count(),
        ]]);
    }
}
