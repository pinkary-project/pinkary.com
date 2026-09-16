<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final readonly class ProfileController
{
    /**
     * Return the authenticated user's profile.
     */
    public function show(Request $request): JsonResource
    {
        /** @var User $user */
        $user = $request->user();

        return new UserResource($user);
    }
}
