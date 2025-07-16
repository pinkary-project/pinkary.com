<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SyncVerifiedUser;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

final readonly class UserIsVerifiedController
{
    /**
     * Handles the verified refresh.
     */
    public function update(#[CurrentUser] User $user): RedirectResponse
    {
        SyncVerifiedUser::dispatchSync($user);

        $freshUser = $user->fresh();

        if ($freshUser === null) {
            return to_route('profile.edit');
        }

        $freshUser->is_verified
            ? session()->flash('flash-message', 'Your account has been verified.')
            : session()->flash('flash-message', 'Your account is not verified yet.');

        return to_route('profile.edit');
    }
}
