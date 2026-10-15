<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Account;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

final readonly class PasswordController
{
    /** Reset a password using a valid password broker token. */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', PasswordRule::defaults(), 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $credentials = [
            'token' => $request->string('token')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'password_confirmation' => $request->string('password_confirmation')->toString(),
        ];

        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            $user->tokens()->delete();

            event(new PasswordReset($user));
        });

        if (! is_string($status)) {
            throw new UnexpectedValueException('The password broker returned an invalid status.');
        }

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    /** Change the authenticated user's password. */
    public function update(Request $request, #[CurrentUser] User $user): Response
    {
        $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
            'password' => ['required', PasswordRule::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($request->string('password')->toString()),
        ]);

        return response()->noContent();
    }

    /** Request a reset link without revealing whether the email is registered. */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::sendResetLink([
            'email' => $request->string('email')->toString(),
        ]);

        return response()->json([
            'message' => 'If an account exists for that email, a password reset link has been sent.',
        ], 202);
    }
}
