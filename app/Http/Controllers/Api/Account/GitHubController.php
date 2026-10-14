<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Account;

use App\Actions\Users\CreateMobileGitHubHandoff;
use App\Actions\Users\UpdateUserGitHubUsername;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

final readonly class GitHubController
{
    /** Return the current GitHub connection and account eligibility. */
    public function show(#[CurrentUser] User $user): JsonResponse
    {
        $connected = $user->github_username !== null;
        $providerAvailable = $this->providerIsConfigured();
        $reason = match (true) {
            ! $providerAvailable => 'provider_unavailable',
            $connected => 'already_connected',
            default => null,
        };

        return response()->json([
            'connected' => $connected,
            'github_username' => $user->github_username,
            'eligible' => $reason === null,
            'eligibility_reason' => $reason,
            'verification' => [
                'profile' => $user->is_verified,
                'company' => $user->is_company_verified,
            ],
        ])->header('Cache-Control', 'private, no-store');
    }

    /** Create a short-lived browser handoff for connecting GitHub. */
    public function store(
        #[CurrentUser] User $user,
        CreateMobileGitHubHandoff $createHandoff,
    ): JsonResponse {
        abort_unless($this->providerIsConfigured(), 503, 'GitHub connection is unavailable.');
        abort_if($user->github_username !== null, 409, 'A GitHub account is already connected.');

        $handoff = $createHandoff->handle($user);
        $path = route('mobile.github.handoff.start', [
            'handoff' => $handoff['id'],
            'token' => $handoff['token'],
        ], absolute: false);

        return response()->json([
            'id' => $handoff['id'],
            'status' => 'pending',
            'browser_url' => mb_rtrim(config()->string('app.url'), '/').$path,
            'expires_at' => $handoff['expires_at']->toIso8601String(),
            'expires_in' => CreateMobileGitHubHandoff::TTL_MINUTES * 60,
        ], 201)->header('Cache-Control', 'private, no-store');
    }

    /** Return handoff status only to the account that started it. */
    public function showHandoff(#[CurrentUser] User $user, string $handoffId): JsonResponse
    {
        $handoff = DB::table('mobile_github_handoffs')
            ->where('id', $handoffId)
            ->where('user_id', $user->id)
            ->first();

        abort_if($handoff === null, 404);

        $status = $handoff->status;
        $expiresAtValue = $handoff->expires_at;

        if (! is_string($expiresAtValue)) {
            abort(500, 'The GitHub handoff expiry is invalid.');
        }

        $expiresAt = CarbonImmutable::parse($expiresAtValue);

        if ($expiresAt->isPast() && in_array($status, ['pending', 'started', 'processing'], true)) {
            $status = 'expired';
        }

        return response()->json([
            'id' => $handoff->id,
            'status' => $status,
            'github_username' => $status === 'completed' ? $handoff->github_username : null,
            'expires_at' => $expiresAt->toIso8601String(),
        ])->header('Cache-Control', 'private, no-store');
    }

    /** Disconnect the GitHub username using the existing account update action. */
    public function destroy(
        #[CurrentUser] User $user,
        UpdateUserGitHubUsername $updateUserGitHubUsername,
    ): Response {
        if ($user->github_username !== null) {
            $updateUserGitHubUsername->handle($user, null);
        }

        return response()->noContent();
    }

    /** Determine whether the configured GitHub OAuth client can be used. */
    private function providerIsConfigured(): bool
    {
        return filled(config('services.github.client_id'))
            && filled(config('services.github.client_secret'))
            && filled(config('services.github.redirect'));
    }
}
