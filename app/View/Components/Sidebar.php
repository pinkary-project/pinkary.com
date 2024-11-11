<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Sidebar extends Component
{
    public function render(): View
    {
        return view('layouts.components.sidebar');
    }
}
