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
    /**
     * Return a user's public profile, readable by guests like the web.
     */
    public function show(Request $request, User $user, UserProfileQuery $userProfileQuery): UserResource
    {
        // The API is sessionless, so `dispatchUsingSession` would mint a
        // brand new session id per guest request and defeat its 2h
        // dedupe. Only count a view when the request carries a stable
        // identity; the web still counts guest views through its session.
        if ($request->user() instanceof User) {
            IncrementViews::dispatchUsingSession($user);
        }

        return new UserResource($userProfileQuery->load($user, $request->user()?->id));
    }
}
