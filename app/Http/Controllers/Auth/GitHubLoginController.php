<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Services\GitHub;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;

final readonly class GitHubLoginController
{
    /**
     * Handles the GitHub login redirect.
     */
    public function index(): RedirectResponse
    {
        /** @var GithubProvider $driver */
        $driver = Socialite::driver('github');

        return $driver->redirectUrl(route('profile.connect.github.login.callback'))->redirect();
    }

    /**
     * Handles the GitHub login callback.
     */
    public function update(Request $request, GitHub $github): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();

        $user = User::where('email', $githubUser->getEmail())->first();
        if (! $user instanceof User) {
            session([
                'github_email' => $githubUser->getEmail(),
                'github_username' => $githubUser->getNickname(),
            ]);

            return to_route('register');
        }
        if ($user->github_username === null) {
            $errors = $github->linkGitHubUser($githubUser->getNickname(), $user, $request);

            if ($errors !== []) {
                return to_route('login')->withErrors($errors, 'github');
            }
        }
        Auth::login($user);

        return to_route('home.feed');
    }
}
