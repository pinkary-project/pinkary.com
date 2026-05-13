@php
    $tabs = [
        ['label' => __('Recent'), 'route' => 'home.feed', 'active' => request()->routeIs('home.feed') || request()->routeIs('hashtag.show')],
        ['label' => __('Following'), 'route' => 'home.following', 'active' => request()->routeIs('home.following')],
        ['label' => __('Trending'), 'route' => 'home.trending', 'active' => request()->routeIs('home.trending')],
    ];
@endphp

<div class="overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
    <div class="inline-flex min-w-full items-center gap-1 sm:min-w-0">
        @foreach ($tabs as $tab)
            <a
                data-pan="home-tabs-{{ str($tab['label'])->lower() }}"
                href="{{ route($tab['route']) }}"
                class="{{ $tab['active'] ? 'bg-slate-950 px-3.5 py-1.5 text-white dark:bg-[#1a2438]' : 'px-2 py-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-[#11192b] dark:hover:text-white' }} inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-semibold leading-4 transition duration-150 ease-in-out focus:outline-none"
                title="{{ $tab['label'] }}"
                wire:navigate
                wire:transition
            >
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
