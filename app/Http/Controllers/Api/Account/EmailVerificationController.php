<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Account;

use App\Models\User;
use App\Notifications\MobileVerifyEmail;
use Illuminate\Auth\Events\Verified;
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

    /** Send a mobile-compatible, signed verification link when needed. */
    public function store(#[CurrentUser] User $user): JsonResponse
    {
        $verified = $user->hasVerifiedEmail();

        if (! $verified) {
            $user->notify(new MobileVerifyEmail);
        }

        return response()->json([
            'message' => 'If this account still needs verification, a link has been sent.',
            'verified' => $verified,
        ], 202);
    }

    /** Complete verification using the signed link sent to the account email. */
    public function complete(User $user, string $hash): JsonResponse
    {
        abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

        $alreadyVerified = $user->hasVerifiedEmail();

        if (! $alreadyVerified && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'verified' => true,
            'already_verified' => $alreadyVerified,
        ]);
    }
}
