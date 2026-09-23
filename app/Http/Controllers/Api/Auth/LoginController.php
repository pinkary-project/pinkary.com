<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Actions\Auth\CreateToken;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final readonly class LoginController
{
    /**
     * @throws ValidationException
     */
    public function __invoke(
        LoginRequest $request,
        AuthenticateUser $authenticateUser,
        CreateToken $createToken,
    ): JsonResponse {
        $validated = $request->validated();

        $user = $authenticateUser->handle($validated['email'], $validated['password']);
        $token = $createToken->handle($user);

        return (new UserResource($user))
            ->additional(['token' => $token])
            ->response();
    }
}
