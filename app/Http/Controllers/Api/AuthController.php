<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Auth\CreateToken;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

final readonly class AuthController
{
    /**
     * Register a new mobile user and issue a personal access token.
     */
    public function register(RegisterRequest $request, CreateToken $createToken): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        return $createToken->handle($user, Response::HTTP_CREATED);
    }

    /**
     * Authenticate a mobile user and issue a personal access token.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request, CreateToken $createToken): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user instanceof User || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $createToken->handle($user);
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(Request $request): Response
    {
        $plainTextToken = $request->bearerToken();

        if (is_string($plainTextToken)) {
            PersonalAccessToken::findToken($plainTextToken)?->delete();
        }

        return response()->noContent();
    }
}
