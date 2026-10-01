<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Actions\Auth\CreateToken;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Queries\Users\UserProfileQuery;
use App\Services\TwoFactorChallenge;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final readonly class LoginController
{
    /**
     * @throws ValidationException
     */
    public function store(
        LoginRequest $request,
        AuthenticateUser $authenticateUser,
        CreateToken $createToken,
        UserProfileQuery $userProfileQuery,
        TwoFactorChallenge $challenge,
    ): JsonResponse {
        $credentials = $request->credentials();

        $user = $authenticateUser->handle($credentials['email'], $credentials['password']);

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return $this->challenged($user, $challenge);
        }

        return $this->authorized($user, $createToken, $userProfileQuery);
    }

    /**
     * Hand back a challenge instead of a token, in a shape a client can branch
     * on rather than a bare validation error.
     */
    private function challenged(User $user, TwoFactorChallenge $challenge): JsonResponse
    {
        return response()->json([
            'message' => 'Two factor authentication is required to finish signing in.',
            'code' => 'two_factor_required',
            'challenge' => $challenge->issue($user),
            'expires_in' => TwoFactorChallenge::TTL_MINUTES * 60,
        ], 422);
    }

    /**
     * Mint the token and return the profile.
     */
    private function authorized(
        User $user,
        CreateToken $createToken,
        UserProfileQuery $userProfileQuery,
    ): JsonResponse {
        return new UserResource($userProfileQuery->load($user, $user->id))
            ->viewingAs($user)
            ->additional(['token' => $createToken->handle($user)])
            ->response();
    }
}
