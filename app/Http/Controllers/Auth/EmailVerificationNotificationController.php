<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

final readonly class EmailVerificationNotificationController
{
    /**
     * Send a new email verification notification.
     */
    public function store(#[CurrentUser] User $user): RedirectResponse
    {
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('profile.show', [
                'username' => $user->username,
            ], absolute: false));
        }

        $user->sendEmailVerificationNotification();

        session()->flash('flash-message', 'Verification email sent.');

        return back();
    }
}
