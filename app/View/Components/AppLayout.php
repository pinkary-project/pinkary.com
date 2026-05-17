<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

final class AppLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $peopleToFollowContext = 'generic',
        public ?int $peopleToFollowUserId = null,
        public ?string $peopleToFollowQuestionId = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
