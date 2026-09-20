<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Users\LoadProfile;
use App\Actions\Users\UpdateUser;
use App\Actions\Users\UpdateUserSettings;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Jobs\UpdateUserAvatar;
use Illuminate\Http\UploadedFile;

final readonly class UpdateController
{
    public function __invoke(
        UpdateProfileRequest $request,
        UpdateUser $updateUser,
        UpdateUserSettings $updateUserSettings,
        LoadProfile $loadProfile,
    ): UserResource {
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

        return new UserResource($loadProfile->handle($user->fresh()));
    }
}
