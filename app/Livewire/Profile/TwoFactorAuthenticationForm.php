<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\View\View;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class TwoFactorAuthenticationForm extends Component
{
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
     * The OTP code for confirming two factor authentication.
     */
    #[Locked]
    public ?string $code = null;

    /**
     * Determine if two factor authentication is enabled.
     */
    #[Locked]
    public bool $enabled = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->enabled = type(auth()->user())->as(User::class)->two_factor_secret !== null;
    }

    /**
     * Enable two factor authentication for the user.
     */
    public function enableTwoFactorAuthentication(EnableTwoFactorAuthentication $enable): void
    {
        $enable(auth()->user());

        $this->showingQrCode = true;
        $this->showingRecoveryCodes = true;
        $this->enabled = true;
    }

    /**
     * Display the user's recovery codes.
     */
    public function showRecoveryCodes(): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->showingRecoveryCodes = true;
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate): void
    {
        if (! $this->enabled) {
            return;
        }

        $generate(auth()->user());

        $this->showingRecoveryCodes = true;
    }

    /**
     * Disable two factor authentication for the user.
     */
    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable): void
    {
        $disable(auth()->user());

        $this->showingQrCode = false;
        $this->showingRecoveryCodes = false;
        $this->enabled = false;
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
