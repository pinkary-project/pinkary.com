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
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

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
        UpdateUserGitHubUsername $updateUserGitHubUsername,
        CompleteMobileGitHubHandoff $completeHandoff,
        FailMobileGitHubHandoff $failHandoff,
    ): SymfonyResponse {
        $handoffId = $request->session()->get('mobile.github.handoff_id');

        if (is_string($handoffId)) {
            return $this->completeMobileHandoff($request, $handoffId, $completeHandoff, $failHandoff);
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

    /** Complete a mobile GitHub handoff from the shared OAuth callback. */
    private function completeMobileHandoff(
        Request $request,
        string $handoffId,
        CompleteMobileGitHubHandoff $completeHandoff,
        FailMobileGitHubHandoff $failHandoff,
    ): SymfonyResponse {
        if ($request->query->has('error')) {
            $state = $request->session()->pull('state');

            abort_unless(
                is_string($state) && hash_equals($state, (string) $request->query('state')),
                403,
                'Invalid OAuth state.',
            );

            $request->session()->forget('mobile.github.handoff_id');
            $failHandoff->handle($handoffId);

            return $this->browserMessage('GitHub authorization was cancelled. Return to Pinkary Mobile.', 200);
        }

        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (InvalidStateException $exception) {
            report($exception);

            return $this->browserMessage('The GitHub sign-in session expired. Return to Pinkary Mobile and try again.', 403);
        } catch (Throwable $exception) {
            $request->session()->forget('mobile.github.handoff_id');
            $failHandoff->handle($handoffId);
            report($exception);

            return $this->browserMessage('GitHub could not be reached. Return to Pinkary Mobile and try again.', 502);
        }

        $request->session()->forget('mobile.github.handoff_id');

        $githubUsername = $githubUser->getNickname();

        if (! is_string($githubUsername) || $githubUsername === '') {
            $failHandoff->handle($handoffId);

            return $this->browserMessage('GitHub did not provide a usable account name. Return to Pinkary Mobile.', 422);
        }

        $validator = Validator::make([
            'github_username' => $githubUsername,
        ], [
            'github_username' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $failHandoff->handle($handoffId);

            return $this->browserMessage('GitHub did not provide a usable account name. Return to Pinkary Mobile.', 422);
        }

        try {
            $completed = $completeHandoff->handle($handoffId, $githubUsername);
        } catch (Throwable $exception) {
            $failHandoff->handle($handoffId);
            report($exception);

            return $this->browserMessage('GitHub connection could not be completed. Return to Pinkary Mobile.', 500);
        }

        if (! $completed) {
            return $this->browserMessage('GitHub connection expired or is no longer eligible. Return to Pinkary Mobile.', 409);
        }

        return $this->browserMessage('GitHub connected. Return to Pinkary Mobile.', 200);
    }

    /** Return a non-cacheable browser result without reflecting request data. */
    private function browserMessage(string $message, int $status): SymfonyResponse
    {
        return response($message, $status)
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
