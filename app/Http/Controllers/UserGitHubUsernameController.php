<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SyncVerifiedUser;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

final readonly class UserGitHubUsernameController
{
    /**
     * Handles the GitHub connection redirect.
     */
    public function index(): SymfonyRedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Handles the GitHub connection update.
     */
    public function update(#[CurrentUser] User $user): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();

        try {
            /** @var array<string, string> $validated */
            $validated = Validator::validate([
                'github_username' => $githubUser->getNickname(),
            ], [
                'github_username' => ['required', 'string', 'max:255', 'unique:users,github_username'],
            ], [
                'github_username.unique' => 'This GitHub username is already connected to another account.',
            ]);
        } catch (ValidationException $e) {
            if ($githubUser->getNickname() === $user->github_username) {
                session()->flash('flash-message', 'The same GitHub account has been connected.');

                return to_route('profile.edit');
            }

            return to_route('profile.edit')->withErrors($e->errors(), 'verified');
        }

        $user->update($validated);

        SyncVerifiedUser::dispatchSync($user);

        $freshUser = $user->fresh();

        if ($freshUser === null) {
            return to_route('profile.edit');
        }

        $freshUser->is_verified
            ? session()->flash('flash-message', 'Your GitHub account has been connected and you are now verified.')
            : session()->flash('flash-message', 'Your GitHub account has been connected.');

        if (! $freshUser->is_uploaded_avatar) {
            UpdateUserAvatar::dispatch(
                $freshUser,
                null,
                'github',
            );
        }

        return to_route('profile.edit');
    }

    /**
     * Handles the GitHub connection destroy.
     */
    public function destroy(#[CurrentUser] User $user): RedirectResponse
    {
        $user->update(['github_username' => null]);
        SyncVerifiedUser::dispatchSync($user);
        session()->flash('flash-message', 'Your GitHub account has been disconnected.');

        return to_route('profile.edit');
    }
}
