<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserAvatarUpdateRequest;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;

final readonly class UserAvatarController
{
    /**
     * Handles the verified refresh.
     */
    public function update(UserAvatarUpdateRequest $request, #[CurrentUser] User $user): RedirectResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('avatar');
        UpdateUserAvatar::dispatchSync($user, $file->getRealPath());

        return to_route('profile.edit')
            ->with('flash-message', 'Avatar updated.');
    }

    /**
     * Delete the existing avatar.
     */
    public function destroy(#[CurrentUser] User $user): RedirectResponse
    {
        UpdateUserAvatar::dispatchSync(
            $user,
            null,
            $user->github_username ? 'github' : 'gravatar',
        );

        return to_route('profile.edit')
            ->with('flash-message', 'Updating avatar using '.($user->github_username ? 'GitHub' : 'Gravatar').'.');
    }
}
