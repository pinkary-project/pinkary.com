<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Users\StartMobileGitHubHandoff;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

final readonly class MobileGitHubHandoffController
{
    /** Redeem the one-time secret and begin GitHub OAuth in the browser session. */
    public function __invoke(
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
}
