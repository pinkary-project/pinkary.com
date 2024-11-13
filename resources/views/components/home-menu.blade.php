<div class="text-sm space-x-1">
    <a
        data-pan="home-tabs-trending"
        href="{{ route('home.trending') }}"
        title="{{ __('Trending') }}"
        class="{{ request()->routeIs('home.trending') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800/30 text-gray-400' }} bg-gray-800 rounded-md px-2.5 py-1.5"
        wire:navigate
        wire:transition
    >
        <span>{{ __('Trending') }}</span>
    </a>
    <a
        data-pan="home-tabs-following"
        href="{{ route('home.following') }}"
        title="{{ __('Following') }}"
        class="{{ request()->routeIs('home.following') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800/30 text-gray-400' }} bg-gray-800 rounded-md px-2.5 py-1.5"
        wire:navigate
        wire:transition
    >
        <span>{{ __('Following') }}</span>
    </a>
    <a
        data-pan="home-tabs-feed"
        href="{{ route('home.feed') }}"
        title="{{ __('Recent') }}"
        class="{{ request()->routeIs('home.feed') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800/30 text-gray-400' }} bg-gray-800 rounded-md px-2.5 py-1.5"
        wire:navigate
        wire:transition
    >
        <span>{{ __('Recent') }}</span>
    </a>
</div>
