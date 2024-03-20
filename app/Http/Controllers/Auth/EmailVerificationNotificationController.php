<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final readonly class EmailVerificationNotificationController
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('profile.show', [
                'user' => $user->username,
            ], absolute: false));
        }

        $user->sendEmailVerificationNotification();

        session()->flash('flash-message', 'Verification email sent.');

        return back();
    }
}
