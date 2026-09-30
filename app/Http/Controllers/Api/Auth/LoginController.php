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

        // The first user object the client ever sees. Loading it like any
        // other profile means links, counts and the follow flags are real
        // rather than absent, and viewingAs() fills in email and the
        // verification state that no token can authenticate yet.
        return new UserResource($userProfileQuery->load($user, $user->id))
            ->viewingAs($user)
            ->additional(['token' => $token])
            ->response();
    }
}
