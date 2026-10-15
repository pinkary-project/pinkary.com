<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

final class VerifyEmailLinkRequest extends EmailVerificationRequest
{
    /**
     * Account resolved from the signed verification link.
     */
    private ?User $verificationUser = null;

    /**
     * Resolve the account from the signed link and use Laravel's email-hash validation.
     */
    public function authorize(): bool
    {
        $user = User::query()->whereKey((string) $this->route('id'))->first();

        if ($user === null) {
            return false;
        }

        $this->verificationUser = $user;
        $this->setUserResolver(static fn (?string $guard = null): User => $user);

        return parent::authorize();
    }

    /**
     * Get the account whose email address is being verified.
     */
    public function verificationUser(): User
    {
        return $this->verificationUser ?? abort(403);
    }
}
