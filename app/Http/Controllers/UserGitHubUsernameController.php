<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Users\CompleteMobileGitHubHandoff;
use App\Actions\Users\FailMobileGitHubHandoff;
use App\Actions\Users\UpdateUserGitHubUsername;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
    public function update(
        Request $request,
        MobileGitHubHandoffController $mobileGitHubHandoff,
        UpdateUserGitHubUsername $updateUserGitHubUsername,
        CompleteMobileGitHubHandoff $completeHandoff,
        FailMobileGitHubHandoff $failHandoff,
    ): SymfonyResponse {
        $handoffId = $request->session()->get('mobile.github.handoff_id');

        if (is_string($handoffId)) {
            return $mobileGitHubHandoff->complete($request, $handoffId, $completeHandoff, $failHandoff);
        }

        $user = $request->user();

        if (! ($user instanceof User)) {
            return to_route('login');
        }

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

        $user = $updateUserGitHubUsername->handle($user, $validated['github_username']);

        $user->is_verified
            ? session()->flash('flash-message', 'Your GitHub account has been connected and you are now verified.')
            : session()->flash('flash-message', 'Your GitHub account has been connected.');

        if (! $user->is_uploaded_avatar) {
            UpdateUserAvatar::dispatchFor($user);
        }

        return to_route('profile.edit');
    }

    /**
     * Handles the GitHub connection destroy.
     */
    public function destroy(
        #[CurrentUser] User $user,
        UpdateUserGitHubUsername $updateUserGitHubUsername,
    ): RedirectResponse {
        $updateUserGitHubUsername->handle($user, null);
        session()->flash('flash-message', 'Your GitHub account has been disconnected.');

        return to_route('profile.edit');
    }
}
