<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\CreateToken;
use App\Http\Requests\Api\RegisterRequest;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

final readonly class RegisterController
{
    public function __invoke(RegisterRequest $request, CreateToken $createToken): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        UpdateUserAvatar::dispatchFor($user);

        return $createToken->handle($user, Response::HTTP_CREATED);
    }
}
