<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Account;

use App\Http\Resources\UserResource;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use App\Queries\Users\UserProfileQuery;
use Illuminate\Container\Attributes\CurrentUser;

final readonly class AvatarController
{
    /** Refetch the avatar selected by the account's GitHub or Gravatar policy. */
    public function __invoke(#[CurrentUser] User $user, UserProfileQuery $userProfileQuery): UserResource
    {
        return $this->update($user, $userProfileQuery);
    }

    /** Remove an uploaded avatar and restore the account's provider avatar. */
    public function destroy(#[CurrentUser] User $user, UserProfileQuery $userProfileQuery): UserResource
    {
        return $this->update($user, $userProfileQuery);
    }

    /** Run the existing avatar policy and return the refreshed profile. */
    private function update(User $user, UserProfileQuery $userProfileQuery): UserResource
    {
        UpdateUserAvatar::dispatchForSync($user);

        $fresh = User::query()->findOrFail($user->id);

        return new UserResource($userProfileQuery->load($fresh, $fresh->id));
    }
}
