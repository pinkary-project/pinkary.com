<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Users\LoadProfile;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final readonly class ProfileController
{
    /**
     * Return the authenticated user's profile with the counts and links
     * the profile card renders.
     */
    public function show(Request $request, LoadProfile $loadProfile): JsonResource
    {
        /** @var User $user */
        $user = $request->user();

        return new UserResource($loadProfile->handle($user));
    }
}
