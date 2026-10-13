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

        // Reject all unusable challenges identically to prevent account probing.
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
