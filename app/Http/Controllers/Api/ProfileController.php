<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Users\UpdateUser;
use App\Actions\Users\UpdateUserSettings;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use App\Queries\Users\UserProfileQuery;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class ProfileController
{
    /** Return the authenticated user's profile. */
    public function show(Request $request, UserProfileQuery $userProfileQuery): UserResource
    {
        /** @var User $user */
        $user = $request->user();

        return new UserResource($userProfileQuery->load($user, $user->id));
    }

    /** Update the signed-in user's profile. */
    public function update(
        UpdateProfileRequest $request,
        UpdateUser $updateUser,
        UpdateUserSettings $updateUserSettings,
        UserProfileQuery $userProfileQuery,
    ): UserResource {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            /** @var UploadedFile $file */
            $file = $request->file('avatar');
            UpdateUserAvatar::dispatchForSync($user, $file->getRealPath());
        }

        $settings = array_filter([
            'link_shape' => $validated['link_shape'] ?? null,
            'gradient' => $validated['gradient'] ?? null,
        ]);

        if ($settings !== []) {
            $updateUserSettings->handle($user, array_merge($user->settings ?? [], $settings));
        }

        $userAttributes = array_diff_key($validated, array_flip(['avatar', 'link_shape', 'gradient']));

        if ($userAttributes !== []) {
            $updateUser->handle($user, $userAttributes);
        }

        $fresh = User::query()->findOrFail($user->id);

        return new UserResource($userProfileQuery->load($fresh, $fresh->id));
    }
}
