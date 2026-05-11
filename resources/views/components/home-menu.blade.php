@php
    $tabs = [
        ['label' => __('Trending'), 'route' => 'home.trending', 'active' => request()->routeIs('home.trending')],
        ['label' => __('Following'), 'route' => 'home.following', 'active' => request()->routeIs('home.following')],
        ['label' => __('Recent'), 'route' => 'home.feed', 'active' => request()->routeIs('home.feed') || request()->routeIs('hashtag.show')],
    ];
@endphp

<div class="overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
    <div class="inline-flex min-w-full gap-2 sm:min-w-0">
        @foreach ($tabs as $tab)
            <a
                data-pan="home-tabs-{{ str($tab['label'])->lower() }}"
                href="{{ route($tab['route']) }}"
                class="{{ $tab['active'] ? 'bg-slate-950 text-white shadow-lg shadow-slate-900/10 dark:bg-slate-800 dark:text-white dark:shadow-black/20' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-white' }} inline-flex items-center justify-center whitespace-nowrap rounded-[1rem] px-4 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                title="{{ $tab['label'] }}"
                wire:navigate
                wire:transition
            >
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
