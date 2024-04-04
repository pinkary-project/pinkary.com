<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Jobs\SyncVerifiedUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

final class GitHubAuthController
{
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();
        $user = User::firstOrCreate([
            'github_username' => $githubUser->getNickname(),
        ], [
            'email' => $githubUser->getEmail(),
            'email_verified_at' => now(),
            'name' => $githubUser->getName(),
            'username' => $githubUser->getNickname(),
        ])->fresh();

        auth()->login($user);

        session()->regenerate();

        if ($user->wasRecentlyCreated) {
            dispatch_sync(new SyncVerifiedUser($user));
        }

        $user->is_verified
            ? session()->flash('flash-message', __('Your GitHub account has been connected and you are now verified.'))
            : session()->flash('flash-message', __('Your GitHub account has been connected.'));

        return redirect()->route('profile.edit');
    }
}
