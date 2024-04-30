<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

final readonly class VerifyEmailController
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        if ($user->hasVerifiedEmail()) {
            session()->flash('flash-message', 'Your email is already verified.');

            return redirect()
                ->intended(route('profile.show', [
                    'username' => $user->username,
                ], absolute: false));
        }

        session()->flash('flash-message', 'Your email has been verified.');

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return to_route('profile.show', [
            'username' => $user->username,
        ]);
    }
}
