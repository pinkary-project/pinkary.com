<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use App\Rules\Recaptcha;
use App\Rules\UnauthorizedEmailProviders;
use App\Rules\Username;
use App\Services\GitHub;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

final readonly class RegisteredUserController
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $githubEmail = session()->pull('github_email');
        $githubUsername = session()->pull('github_username');

        return view('auth.register')->with('githubEmail', $githubEmail)->with('githubUsername', $githubUsername);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, GitHub $gitHub): RedirectResponse
    {
        $githubUsername = $request->github_username;
        if ($githubUsername) {
            session([
                'github_email' => $request->email,
                'github_username' => $request->github_username,
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:4', 'max:50', 'unique:'.User::class, new Username],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, new UnauthorizedEmailProviders()],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
            'g-recaptcha-response' => app()->environment('production') ? ['required', new Recaptcha($request->ip())] : [],
        ]);

        $request->session()->forget(['github_email', 'github_username']);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->string('password')->value()),
        ]);

        if (is_string($githubUsername) && $githubUsername !== '') {
            $errors = $gitHub->linkGitHubUser($githubUsername, $user, $request);
            if ($errors !== []) {
                $user->delete();

                return to_route('register')->withErrors($errors, 'github');
            }
            $user->email_verified_at = Carbon::now();
            $user->save();
        }

        event(new Registered($user));

        Auth::login($user);

        UpdateUserAvatar::dispatch($user);

        return redirect(route('profile.show', [
            'username' => $user->username,
        ], absolute: false));
    }
}
