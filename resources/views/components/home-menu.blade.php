@php
    $menuItems = [
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
@endphp
<div class="mb-8 flex justify-between space-x-2">
    @foreach($menuItems as $menuItemKey => $menuItem)
        <a
            data-pan="home-tabs-{{ $menuItemKey }}"
            href="{{ route($menuItem['route']) }}"
            class="{{ request()->routeIs($menuItem['route']) ? 'bg-pink-600 text-slate-100' : 'dark:text-slate-500 text-slate-400 dark:hover:text-slate-100 hover:text-slate-800 dark:bg-slate-900 bg-slate-50 ' }} inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border dark:border-transparent border-slate-200 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
            title="{{ $menuItem['label'] }}"
            wire:navigate
            wire:transition
        >
            <x-dynamic-component :component="$menuItem['icon']" class="h-6 w-6 xsm:mr-2" />
            <span class="hidden xsm:inline">{{ $menuItem['label'] }}</span>
        </a>
    @endforeach
</div>
