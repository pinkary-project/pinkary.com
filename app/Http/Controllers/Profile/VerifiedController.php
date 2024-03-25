<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Jobs\SyncVerifiedUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final readonly class VerifiedController
{
    /**
     * Handles the verified refresh.
     */
    public function update(): RedirectResponse
    {
        $user = request()->user();
        assert($user instanceof User);

        dispatch_sync(new SyncVerifiedUser($user));

        $user = $user->fresh();
        assert($user instanceof User);

        $user->is_verified
            ? session()->flash('flash-message', 'Your account has been verified.')
            : session()->flash('flash-message', 'Your account is not verified yet.');

        return redirect()->route('profile.edit');
    }
}
