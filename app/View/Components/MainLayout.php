<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

final class MainLayout extends Component
{
    /**
     * Create a new component instance.
     * Set the background image for the layout.
     */
    public function __construct(public string $backgroundImage = '') {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.main');
    }
}
