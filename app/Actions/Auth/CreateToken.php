<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CreateToken
{
    public function handle(User $user, int $status = Response::HTTP_OK): JsonResponse
    {
        return (new UserResource($user))->additional(['token' => $user->createToken('pinkary-mobile')->plainTextToken])->response()->setStatusCode($status);
    }
}
