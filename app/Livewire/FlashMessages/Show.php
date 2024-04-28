<?php

declare(strict_types=1);

namespace App\Livewire\FlashMessages;

use Illuminate\View\View;
use Livewire\Component;

final class Show extends Component
{
    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.flash-messages.show');
    }
}
