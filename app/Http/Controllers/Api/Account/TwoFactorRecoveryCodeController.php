<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Account;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

final readonly class TwoFactorRecoveryCodeController
{
    /** Reveal recovery codes after confirming the current password. */
    public function store(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
        ]);

        abort_unless($user->hasEnabledTwoFactorAuthentication(), 409, 'Two-factor authentication is not enabled.');

        return $this->noStore(response()->json([
            'recovery_codes' => $user->recoveryCodes(),
        ]));
    }

    /** Replace recovery codes after confirming the current password. */
    public function update(
        Request $request,
        #[CurrentUser] User $user,
        GenerateNewRecoveryCodes $generate,
    ): JsonResponse {
        $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
        ]);

        abort_unless($user->hasEnabledTwoFactorAuthentication(), 409, 'Two-factor authentication is not enabled.');

        $generate($user);

        return $this->noStore(response()->json([
            'recovery_codes' => $user->refresh()->recoveryCodes(),
        ]));
    }

    /** Prevent recovery codes from being cached. */
    private function noStore(JsonResponse $response): JsonResponse
    {
        return $response->header('Cache-Control', 'no-store, private');
    }
}
