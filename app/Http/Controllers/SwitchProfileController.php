<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SwitchProfileController
{
    /**
     * Switch the user's profile to a different user.
     */
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        $logins = session()->get('accounts', []);
        $logins = is_array($logins) ? $logins : [];

        if (! in_array($user->id, $logins)) {
            abort(403, 'You are not allowed to switch to this account.');
        }

        auth()->login($user);
        $request->session()->regenerate();

        return back();
    }
}
