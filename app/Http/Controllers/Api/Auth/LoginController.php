<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Actions\Auth\CreateToken;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Queries\Users\UserProfileQuery;
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
    ): JsonResponse {
        $credentials = $request->credentials();

        $user = $authenticateUser->handle($credentials['email'], $credentials['password']);
        $token = $createToken->handle($user);

        // viewingAs() fills in email and verification state, which no token can
        // authenticate at this point in the request.
        return new UserResource($userProfileQuery->load($user, $user->id))
            ->viewingAs($user)
            ->additional(['token' => $token])
            ->response();
    }
}
