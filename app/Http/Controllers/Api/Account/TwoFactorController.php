<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Account;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Fortify;

final readonly class TwoFactorController
{
    /** Confirm the authenticator code and return the initial recovery codes. */
    public function __invoke(
        Request $request,
        #[CurrentUser] User $user,
        ConfirmTwoFactorAuthentication $confirm,
    ): JsonResponse {
        $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
            'code' => ['required', 'digits:6'],
        ]);

        abort_if($user->two_factor_secret === null, 409, 'Two-factor setup has not been started.');
        abort_if($user->hasEnabledTwoFactorAuthentication(), 409, 'Two-factor authentication is already enabled.');

        $confirm($user, $request->string('code')->toString());

        return $this->noStore(response()->json([
            'enabled' => true,
            'recovery_codes' => $user->refresh()->recoveryCodes(),
        ]));
    }

    /** Return the authenticated user's two-factor status. */
    public function show(#[CurrentUser] User $user): JsonResponse
    {
        return response()->json([
            'enabled' => $user->hasEnabledTwoFactorAuthentication(),
            'pending_confirmation' => $user->two_factor_secret !== null && ! $user->hasEnabledTwoFactorAuthentication(),
        ]);
    }

    /** Start two-factor setup after confirming the current password. */
    public function store(
        Request $request,
        #[CurrentUser] User $user,
        EnableTwoFactorAuthentication $enable,
    ): JsonResponse {
        $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
        ]);

        abort_if($user->hasEnabledTwoFactorAuthentication(), 409, 'Two-factor authentication is already enabled.');

        $enable($user);
        $secret = $user->two_factor_secret;

        if (! is_string($secret)) {
            abort(409, 'Two-factor setup could not be started.');
        }

        return $this->noStore(response()->json([
            'pending_confirmation' => true,
            'secret' => Fortify::currentEncrypter()->decrypt($secret),
            'qr_code_url' => $user->twoFactorQrCodeUrl(),
            'qr_code_svg' => $user->twoFactorQrCodeSvg(),
        ]));
    }

    /** Disable two-factor authentication after confirming the current password. */
    public function destroy(
        Request $request,
        #[CurrentUser] User $user,
        DisableTwoFactorAuthentication $disable,
    ): Response {
        $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
        ]);

        if (
            $user->two_factor_secret !== null
            || $user->two_factor_recovery_codes !== []
            || $user->two_factor_confirmed_at !== null
        ) {
            $disable($user);
        }

        return response()->noContent();
    }

    /** Prevent authenticator secrets and recovery codes from being cached. */
    private function noStore(JsonResponse $response): JsonResponse
    {
        return $response->header('Cache-Control', 'no-store, private');
    }
}
