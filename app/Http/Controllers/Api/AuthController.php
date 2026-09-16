<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Rules\NoEmailAlias;
use App\Rules\NotBlockedAccount;
use App\Rules\UnauthorizedEmailProviders;
use App\Rules\Username;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

final readonly class AuthController
{
    /**
     * Register a new mobile user and issue a personal access token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:4', 'max:50', 'unique:'.User::class, new Username],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, new NoEmailAlias(), new UnauthorizedEmailProviders(), new NotBlockedAccount],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        return $this->tokenResponse($user, Response::HTTP_CREATED);
    }

    /**
     * Authenticate a mobile user and issue a personal access token.
     *
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user instanceof User || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->tokenResponse($user);
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

    private function tokenResponse(User $user, int $status = Response::HTTP_OK): JsonResponse
    {
        return (new UserResource($user))
            ->additional([
                'token' => $user->createToken('pinkary-mobile')->plainTextToken,
            ])
            ->response()
            ->setStatusCode($status);
    }
}
