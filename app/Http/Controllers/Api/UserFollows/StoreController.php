<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\UserFollows;

use App\Actions\Users\CreateFollow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class StoreController
{
    public function __invoke(Request $request, User $user, CreateFollow $createFollow): JsonResponse
    {
        Gate::authorize('follow', $user);

        $createFollow->handle($request->user(), $user->id);

        return response()->json(['data' => [
            'followed' => true,
            'followers' => $user->followers()->count(),
        ]]);
    }
}
