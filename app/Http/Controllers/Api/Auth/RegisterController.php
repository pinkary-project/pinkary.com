<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\CreateToken;
use App\Actions\Users\CreateUser;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class RegisterController
{
    public function __invoke(
        RegisterRequest $request,
        CreateUser $createUser,
        CreateToken $createToken,
    ): JsonResponse {
        $user = $createUser->handle($request->validated());
        $token = $createToken->handle($user);

        return (new UserResource($user))
            ->additional(['token' => $token])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
