<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Users\LoadProfile;
use App\Http\Resources\UserResource;
use App\Jobs\IncrementViews;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final readonly class UserController
{
    public function show(Request $request, User $user, LoadProfile $loadProfile): JsonResource
    {
        IncrementViews::dispatchUsingSession($user);

        return new UserResource($loadProfile->handle($user));
    }
}
