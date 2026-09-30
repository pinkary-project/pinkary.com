<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\CreateToken;
use App\Actions\Users\CreateUser;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Queries\Users\UserProfileQuery;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class RegisterController
{
    /** Register a user and hand back a token. */
    public function store(
        RegisterRequest $request,
        CreateUser $createUser,
        CreateToken $createToken,
        UserProfileQuery $userProfileQuery,
    ): JsonResponse {
        $user = $createUser->handle($request->attributes());
        $token = $createToken->handle($user);

        // A brand new account is unverified by definition, and that state
        // is the one thing it most needs to be told about. viewingAs()
        // fills in email and verification.email, which no token can
        // authenticate at this point in the request.
        return new UserResource($userProfileQuery->load($user, $user->id))
            ->viewingAs($user)
            ->additional(['token' => $token])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
