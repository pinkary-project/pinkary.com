<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\CreateToken;
use App\Actions\Auth\VerifyTwoFactorCode;
use App\Http\Requests\Api\TwoFactorChallengeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Queries\Users\UserProfileQuery;
use App\Services\TwoFactorChallenge;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final readonly class TwoFactorChallengeController
{
    /**
     * Answer a two factor challenge and mint the token.
     *
     * @throws ValidationException
     */
    public function store(
        TwoFactorChallengeRequest $request,
        TwoFactorChallenge $challenge,
        VerifyTwoFactorCode $verifyTwoFactorCode,
        CreateToken $createToken,
        UserProfileQuery $userProfileQuery,
    ): JsonResponse {
        $token = $request->challenge();

        $user = $challenge->resolve($token);

        // Every unusable challenge looks the same from out here, so a caller
        // cannot tell a forged token from an expired one and probe for valid
        // user ids.
        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'challenge' => ['The two factor challenge is invalid or has expired.'],
            ]);
        }

        $verifyTwoFactorCode->handle(
            $user,
            $request->code(),
            $request->recoveryCode(),
        );

        $challenge->spend($token);

        return new UserResource($userProfileQuery->load($user, $user->id))
            ->viewingAs($user)
            ->additional(['token' => $createToken->handle($user)])
            ->response();
    }
}
