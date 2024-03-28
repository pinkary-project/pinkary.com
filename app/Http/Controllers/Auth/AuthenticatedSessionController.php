<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        session()->regenerate();

        $user = $request->user();
        assert($user instanceof User);

        return redirect()->intended(route('profile.show', [
            'username' => $user->username,
        ], absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        session()->invalidate();

        session()->regenerateToken();

        return back();
    }
}
