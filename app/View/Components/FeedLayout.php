<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class FeedLayout extends Component
{
    /**
     * Render the feed layout component.
     */
    public function render(): View
    {
        return view('layouts.feed');
    }
}
