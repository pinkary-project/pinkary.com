<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SyncVerifiedUser;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

final readonly class UserGitHubUsernameController
{
    /**
     * Handles the GitHub connection redirect.
     */
    public function index(): RedirectResponse
    {
        return type(Socialite::driver('github')->redirect())->as(RedirectResponse::class);
    }

    /**
     * Handles the GitHub connection update.
     */
    public function update(Request $request): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();

        $user = type($request->user())->as(User::class);

        try {
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

        $user = type($user->fresh())->as(User::class);

        $user->is_verified
            ? session()->flash('flash-message', 'Your GitHub account has been connected and you are now verified.')
            : session()->flash('flash-message', 'Your GitHub account has been connected.');

        if (! $user->is_uploaded_avatar) {
            UpdateUserAvatar::dispatch(
                user: $user,
                service: 'github',
            );
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
