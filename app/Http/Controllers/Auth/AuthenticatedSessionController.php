<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Services\Accounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class AuthenticatedSessionController
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(): RedirectResponse
    {
        $user = auth()->user();

        $accounts = Accounts::all();
        unset($accounts[$user->username]);

        Accounts::remove($user->username);

        if (filled($accounts)) {
            $lastAccount = array_key_last($accounts);
            Accounts::switch($lastAccount);
        } else {
            auth()->guard('web')->logout();
            session()->invalidate();
            session()->regenerateToken();
            cookie()->queue(cookie()->forget('accounts'));
        }

        return back();
    }
}
