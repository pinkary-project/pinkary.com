<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Livewire\Attributes\Locked;

trait CanBePinnable
{
    /**
     * Whether the pinned label should be displayed or not.
     */
    #[Locked]
    public bool $pinnable = false;
}
