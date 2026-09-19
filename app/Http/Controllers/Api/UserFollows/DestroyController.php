<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\UserFollows;

use App\Actions\Users\DeleteFollow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class DestroyController
{
    public function __invoke(Request $request, User $user, DeleteFollow $deleteFollow): JsonResponse
    {
        Gate::authorize('unfollow', $user);

        $deleteFollow->handle($request->user(), $user->id);

        return response()->json(['data' => [
            'followed' => false,
            'followers' => $user->followers()->count(),
        ]]);
    }
}
