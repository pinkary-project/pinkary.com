<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserAvatarUpdateRequest;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class UserAvatarController
{
    /**
     * Handles the verified refresh.
     */
    public function update(UserAvatarUpdateRequest $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $file = type($request->file('avatar'))->as(UploadedFile::class);
        UpdateUserAvatar::dispatchSync($user, $file->getRealPath());

        return to_route('profile.edit')
            ->with('flash-message', 'Avatar updated.');
    }

    /**
     * Delete the existing avatar.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        UpdateUserAvatar::dispatchSync(
            $user,
            service: $user->github_username ? 'github' : 'gravatar',
        );

        return to_route('profile.edit')
            ->with('flash-message', 'Updating avatar using '.($user->github_username ? 'GitHub' : 'Gravatar').'.');
    }
}
