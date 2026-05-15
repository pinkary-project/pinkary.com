<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class HomeMenu extends Component
{
    /**
     * An array of menu items.
     *
     * @var array<string, array{label: string, route: string, icon: string}>
     */
    public array $menuItems;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->menuItems = [
            'feed' => [
                'label' => __('Feed'),
                'route' => 'home.feed',
                'icon' => 'heroicon-o-home',
            ],
            'following' => [
                'label' => __('Following'),
                'route' => 'home.following',
                'icon' => 'heroicon-o-heart',
            ],
            'trending' => [
                'label' => __('Trending'),
                'route' => 'home.trending',
                'icon' => 'heroicon-m-fire',
            ],
            'search' => [
                'label' => __('Search'),
                'route' => 'home.users',
                'icon' => 'heroicon-o-magnifying-glass',
            ],
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.home-menu');
    }
}
