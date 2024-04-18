<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Actions\Profile\DeleteAvatar;
use App\Actions\Profile\StoreAvatar;
use App\Http\Requests\AvatarUploadRequest;
use App\Jobs\DownloadUserAvatar;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class AvatarController
{
    /**
     * Handles the verified refresh.
     */
    public function update(AvatarUploadRequest $request): RedirectResponse
    {
        $user = type(request()->user())->as(User::class);

        if ($user->avatar) {
            DeleteAvatar::execute($user->avatar);
        }

        /** @var UploadedFile $file */
        $file = $request->file('avatar');
        $location = StoreAvatar::execute($file, $user->id);

        $user->update([
            'avatar' => 'storage/'.$location,
            'avatar_updated_at' => now(),
            'has_custom_avatar' => true,
        ]);

        $user->save();

        return to_route('profile.edit')
            ->with('flash-message', 'Avatar uploaded.');
    }

    /**
     * Delete the existing avatar.
     */
    public function delete(Request $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        DeleteAvatar::execute($user->avatar);

        $user->update([
            'avatar' => null,
            'avatar_updated_at' => null,
            'has_custom_avatar' => false,
        ]);

        $user->save();

        DownloadUserAvatar::dispatch($user);

        return to_route('profile.edit')
            ->with('flash-message', 'Avatar deleted.');
    }
}
