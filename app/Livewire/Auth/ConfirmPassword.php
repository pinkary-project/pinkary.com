<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class ConfirmPassword extends Component
{
    /**
     * The Password being confirmed.
     */
    public string $password;

    /**
     * The ID to confirm.
     */
    #[Locked]
    public string $idToConfirm;

    /**
     * Initialize the confirmation.
     */
    #[On('confirm-password')]
    public function initialize(string $idToConfirm): void
    {
        $this->idToConfirm = $idToConfirm;
        $this->password = '';
        $this->dispatch('open-modal', 'confirm-password');
    }

    /**
     * Confirm the password.
     */
    public function confirm(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        session()->put('auth.password_confirmed_at', time());
        $this->password = '';
        $this->dispatch('close-modal', 'confirm-password');
        $this->dispatch('password-confirmed-'.$this->idToConfirm);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.auth.confirm-password');
    }
}
