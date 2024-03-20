<?php

declare(strict_types=1);

namespace App\Livewire\FlashMessages;

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Show extends Component
{
    /**
     * Flashes a new message.
     */
    #[On('notification.created')]
    public function flash(string $message): void
    {
        session()->flash('flash-message', $message);

        $this->js('setTimeout(() => { $wire.flush() }, 3000)');
    }

    /**
     * Flushes the flash message.
     */
    public function flush(): void
    {
        session()->forget('flash-message');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.flash-messages.show');
    }
}
