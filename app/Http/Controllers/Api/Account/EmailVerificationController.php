<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Account;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final readonly class EmailVerificationController
{
    /** Return the authenticated user's email verification status. */
    public function show(#[CurrentUser] User $user): JsonResponse
    {
        return response()->json([
            'email' => $user->email,
            'verified' => $user->hasVerifiedEmail(),
            'verified_at' => $user->email_verified_at?->toIso8601String(),
        ]);
    }

    /** Send Laravel's signed verification link when needed. */
    public function store(#[CurrentUser] User $user): JsonResponse
    {
        $verified = $user->hasVerifiedEmail();

        if (! $verified) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => 'If this account still needs verification, a link has been sent.',
            'verified' => $verified,
        ], 202);
    }
}
