<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait ConfirmsPasswords
{
    /**
     * Ensure that the user's password has been recently confirmed.
     */
    protected function ensurePasswordIsConfirmed(string $idToConfirm, ?int $maximumSecondsSinceConfirmation = null): bool
    {
        if ($this->passwordIsConfirmed($maximumSecondsSinceConfirmation)) {
            return true;
        }

        $this->dispatch('confirm-password', idToConfirm: $idToConfirm);

        return false;
    }

    /**
     * Determine if the user's password has been recently confirmed.
     */
    protected function passwordIsConfirmed(?int $maximumSecondsSinceConfirmation = null): bool
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation !== null && $maximumSecondsSinceConfirmation !== 0 ? $maximumSecondsSinceConfirmation : config()->integer('auth.password_timeout', 900);

        $confidedAt = is_int(session('auth.password_confirmed_at')) ? session('auth.password_confirmed_at') : 0;

        return (time() - $confidedAt) < $maximumSecondsSinceConfirmation;
    }
}
