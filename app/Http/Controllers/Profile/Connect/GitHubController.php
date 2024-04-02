<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile\Connect;

use App\Jobs\SyncVerifiedUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

final readonly class GitHubController
{
    /**
     * Handles the GitHub connection redirect.
     */
    public function index(): RedirectResponse
    {
        $response = Socialite::driver('github')->redirect();
        $response = type($response)->as(RedirectResponse::class);

        return $response;
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

                return redirect()->route('profile.edit');
            }

            return redirect()->route('profile.edit')->withErrors($e->errors(), 'verified');
        }

        $user->update($validated);

        dispatch_sync(new SyncVerifiedUser($user));

        $user = type($user->fresh())->as(User::class);

        $user->is_verified
            ? session()->flash('flash-message', 'Your GitHub account has been connected and you are now verified.')
            : session()->flash('flash-message', 'Your GitHub account has been connected.');

        return redirect()->route('profile.edit');
    }

    /**
     * Handles the GitHub connection destroy.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = type(request()->user())->as(User::class);

        $user->update(['github_username' => null]);

        dispatch_sync(new SyncVerifiedUser($user));

        session()->flash('flash-message', 'Your GitHub account has been disconnected.');

        return redirect()->route('profile.edit');
    }
}
