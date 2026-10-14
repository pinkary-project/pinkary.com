<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Users\CompleteMobileGitHubHandoff;
use App\Actions\Users\FailMobileGitHubHandoff;
use App\Actions\Users\StartMobileGitHubHandoff;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

final readonly class MobileGitHubHandoffController
{
    /** Redeem the one-time secret and begin GitHub OAuth in the browser session. */
    public function start(
        Request $request,
        string $handoff,
        string $token,
        StartMobileGitHubHandoff $startHandoff,
    ): RedirectResponse {
        abort_unless($startHandoff->handle($handoff, $token), 404);

        $request->session()->put('mobile.github.handoff_id', $handoff);

        $response = Socialite::driver('github')->redirect();
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }

    /** Complete the OAuth callback and make its result available to the account poll. */
    public function complete(
        Request $request,
        string $handoffId,
        CompleteMobileGitHubHandoff $completeHandoff,
        FailMobileGitHubHandoff $failHandoff,
    ): Response {
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
    private function browserMessage(string $message, int $status): Response
    {
        return response($message, $status)
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
