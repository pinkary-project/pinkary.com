<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Jobs\IncrementViews;
use App\Models\User;
use App\Queries\Users\UserProfileQuery;
use Illuminate\Http\Request;

final readonly class UserController
{
    /** Return a user's public profile. */
    public function show(Request $request, User $user, UserProfileQuery $userProfileQuery): UserResource
    {
        // Sessionless guest requests cannot use session-based view deduplication.
        if ($request->user() instanceof User) {
            IncrementViews::dispatchUsingSession($user);
        }

        return new UserResource($userProfileQuery->load($user, $request->user()?->id));
    }
}
