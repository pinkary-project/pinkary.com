<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Livewire\Concerns\ConfirmsPasswords;
use App\Models\User;
use Illuminate\View\View;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class TwoFactorAuthenticationForm extends Component
{
    use ConfirmsPasswords;

    /**
     * Indicates if two factor authentication QR code is being displayed.
     */
    #[Locked]
    public bool $showingQrCode = false;

    /**
     * Indicates if two factor authentication recovery codes are being displayed.
     */
    #[Locked]
    public bool $showingRecoveryCodes = false;

    /**
     * Indicates if the two factor authentication confirmation input and button are being displayed.
     */
    #[Locked]
    public bool $showingConfirmation = false;

    /**
     * Determine if two factor authentication is enabled.
     */
    #[Locked]
    public bool $enabled = false;

    /**
     * The OTP code for confirming two factor authentication.
     */
    public ?string $code = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = type(auth()->user())->as(User::class);
        $this->enabled = $user->hasEnabledTwoFactorAuthentication();
    }

    /**
     * Enable two factor authentication for the user.
     */
    #[On('password-confirmed-enable-two-factor-authentication')]
    public function enableTwoFactorAuthentication(EnableTwoFactorAuthentication $enable): void
    {
        if (! $this->ensurePasswordIsConfirmed('enable-two-factor-authentication')) {
            return;
        }

        $enable(auth()->user());
        $this->showingQrCode = true;
        $this->showingConfirmation = true;
        $this->enabled = true;
    }

    /**
     * Confirm two factor authentication for the user.
     */
    public function confirmTwoFactorAuthentication(ConfirmTwoFactorAuthentication $confirm): void
    {
        $confirm(auth()->user(), $this->pull('code'));

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = true;
    }

    /**
     * Display the user's recovery codes.
     */
    #[On('password-confirmed-show-recovery-codes')]
    public function showRecoveryCodes(): void
    {
        if (! $this->enabled) {
            return;
        }

        if (! $this->ensurePasswordIsConfirmed('show-recovery-codes')) {
            return;
        }

        $this->showingRecoveryCodes = true;
    }

    /**
     * Generate new recovery codes for the user.
     */
    #[On('password-confirmed-generate-new-recovery-codes')]
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate): void
    {
        if (! $this->enabled) {
            return;
        }

        if (! $this->ensurePasswordIsConfirmed('generate-new-recovery-codes')) {
            return;
        }

        $generate(auth()->user());

        $this->showingRecoveryCodes = true;
    }

    /**
     * Disable two factor authentication for the user.
     */
    #[On('password-confirmed-disable-two-factor-authentication')]
    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable): void
    {
        if (! $this->enabled) {
            return;
        }

        if (! $this->ensurePasswordIsConfirmed('disable-two-factor-authentication')) {
            return;
        }

        $disable(auth()->user());

        $this->showingQrCode = false;
        $this->showingRecoveryCodes = false;
        $this->enabled = false;
        $this->code = null;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.profile.two-factor-authentication-form', [
            'user' => auth()->user(),
        ]);
    }
}
