<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SyncVerifiedUser;
use App\Models\User;
use App\Services\GitHub;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;

final readonly class UserGitHubUsernameController
{
    /**
     * Handles the GitHub connection redirect.
     */
    public function index(): RedirectResponse
    {
        /** @var GithubProvider $driver */
        $driver = Socialite::driver('github');

        return $driver->redirectUrl(route('profile.connect.github.update'))->redirect();
    }

    /**
     * Handles the GitHub connection update.
     */
    public function update(Request $request, GitHub $github): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();

        $user = type($request->user())->as(User::class);

        $githubUsername = $githubUser->getNickname();

        $errors = $github->linkGitHubUser($githubUsername, $user, $request);

        if ($errors !== []) {
            if ($githubUsername === $user->github_username) {
                session()->flash('flash-message', 'The same GitHub account has been connected.');
            }

            return to_route('profile.edit')->withErrors($errors, 'verified');
        }

        return to_route('profile.edit');
    }

    /**
     * Handles the GitHub connection destroy.
     */
    public function destroy(): RedirectResponse
    {
        $user = type(request()->user())->as(User::class);
        $user->update(['github_username' => null]);
        SyncVerifiedUser::dispatchSync($user);
        session()->flash('flash-message', 'Your GitHub account has been disconnected.');

        return to_route('profile.edit');
    }
}
