<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

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
        auth()->guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return back();
    }
}
