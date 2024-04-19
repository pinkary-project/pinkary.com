<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Requests\UpdateUserAvatarRequest;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class AvatarController
{
    /**
     * Handles the verified refresh.
     */
    public function update(UpdateUserAvatarRequest $request): RedirectResponse
    {
        $user = type(request()->user())->as(User::class);

        $file = type($request->file('avatar'))->as(UploadedFile::class);
        UpdateUserAvatar::dispatchSync($user, $file->getRealPath());

        return to_route('profile.edit')
            ->with('flash-message', 'Avatar updated.');
    }

    /**
     * Delete the existing avatar.
     */
    public function delete(Request $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        UpdateUserAvatar::dispatchSync($user);

        return to_route('profile.edit')
            ->with('flash-message', 'Avatar deleted.');
    }
}
