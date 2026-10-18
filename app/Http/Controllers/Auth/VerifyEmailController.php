<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\VerifyEmailLinkRequest;
use Illuminate\Http\RedirectResponse;

final readonly class VerifyEmailController
{
    /**
     * Mark the email address in the signed verification link as verified.
     */
    public function __invoke(VerifyEmailLinkRequest $request): RedirectResponse
    {
        $user = $request->verificationUser();

        if ($user->hasVerifiedEmail()) {
            session()->flash('flash-message', 'Your email is already verified.');

            return redirect()
                ->intended(route('profile.show', [
                    'username' => $user->username,
                ], absolute: false));
        }

        session()->flash('flash-message', 'Your email has been verified.');
        $request->fulfill();

        return to_route('profile.show', [
            'username' => $user->username,
        ]);
    }
}
