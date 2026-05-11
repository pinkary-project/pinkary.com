@php
    $tabs = [
        ['label' => __('Feed'), 'route' => 'home.feed', 'icon' => 'home', 'active' => request()->routeIs('home.feed')],
        ['label' => __('Following'), 'route' => 'home.following', 'icon' => 'heart', 'active' => request()->routeIs('home.following')],
        ['label' => __('Trending'), 'route' => 'home.trending', 'icon' => 'fire', 'active' => request()->routeIs('home.trending')],
        ['label' => __('Search'), 'route' => 'home.users', 'icon' => 'search', 'active' => request()->routeIs('home.users')],
    ];
@endphp

<div class="overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
    <div class="inline-flex min-w-full gap-2 rounded-[1.75rem] border border-slate-200/70 bg-slate-50/80 p-2 dark:border-slate-800/70 dark:bg-slate-900/70">
        @foreach ($tabs as $tab)
            <a
                data-pan="home-tabs-{{ str($tab['label'])->lower() }}"
                href="{{ route($tab['route']) }}"
                class="{{ $tab['active'] ? 'bg-slate-950 text-white shadow-lg shadow-slate-900/10 dark:bg-slate-50 dark:text-slate-950 dark:shadow-black/20' : 'text-slate-500 hover:bg-white hover:text-slate-950 dark:text-slate-400 dark:hover:bg-slate-950 dark:hover:text-white' }} inline-flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-[1.25rem] px-4 py-3 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                title="{{ $tab['label'] }}"
                wire:navigate
                wire:transition
            >
                @if ($tab['icon'] === 'home')
                    <x-heroicon-o-home class="h-5 w-5" />
                @elseif ($tab['icon'] === 'heart')
                    <x-heroicon-o-heart class="h-5 w-5" />
                @elseif ($tab['icon'] === 'fire')
                    <x-heroicon-m-fire class="h-5 w-5" color="currentColor" />
                @else
                    <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                @endif

                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
