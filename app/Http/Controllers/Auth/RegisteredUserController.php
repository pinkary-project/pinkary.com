<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use App\Rules\UnauthorizedEmailProviders;
use App\Rules\Username;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile;

final readonly class RegisteredUserController
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, Turnstile $turnstile): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:4', 'max:50', 'unique:'.User::class, new Username],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, new UnauthorizedEmailProviders()],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
            'cf-turnstile-response' => app()->environment(['production', 'testing']) ? ['required', $turnstile] : [],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->string('password')->value()),
        ]);

        event(new Registered($user));

        Auth::login($user);

        UpdateUserAvatar::dispatch($user);

        return redirect(route('profile.show', [
            'username' => $user->username,
        ], absolute: false));
    }
}
