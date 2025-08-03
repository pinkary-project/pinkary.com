<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class EmailVerificationPromptController
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(#[CurrentUser] User $user): RedirectResponse|View
    {
        return $user->hasVerifiedEmail()
                    ? redirect()->intended(route('profile.show', [
                        'username' => $user->username,
                    ], absolute: false)) : view('auth.verify-email');
    }
}
