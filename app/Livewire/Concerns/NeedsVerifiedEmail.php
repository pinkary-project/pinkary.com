<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait NeedsVerifiedEmail
{
    /**
     * Determine if the user does not have a verified email address.
     */
    public function doesNotHaveVerifiedEmail(): bool
    {
        if (auth()->user()?->hasVerifiedEmail()) {
            return false;
        }

        session()->flash('flash-message', 'You must verify your email address before you can continue.');

        $this->redirectRoute('verification.notice', navigate: true);

        return true;
    }
}
