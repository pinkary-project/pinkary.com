<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SyncVerifiedUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final readonly class UserIsVerifiedController
{
    /**
     * Handles the verified refresh.
     */
    public function update(): RedirectResponse
    {
        $user = type(request()->user())->as(User::class);

        SyncVerifiedUser::dispatchSync($user);

        $user = type($user->fresh())->as(User::class);

        $user->is_verified
            ? session()->flash('flash-message', 'Your account has been verified.')
            : session()->flash('flash-message', 'Your account is not verified yet.');

        return to_route('profile.edit');
    }
}
