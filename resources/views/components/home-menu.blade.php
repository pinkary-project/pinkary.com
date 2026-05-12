@php
    $tabs = [
        ['label' => __('Trending'), 'route' => 'home.trending', 'active' => request()->routeIs('home.trending')],
        ['label' => __('Following'), 'route' => 'home.following', 'active' => request()->routeIs('home.following')],
        ['label' => __('Recent'), 'route' => 'home.feed', 'active' => request()->routeIs('home.feed') || request()->routeIs('hashtag.show')],
    ];
@endphp

<div class="overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
    <div class="inline-flex min-w-full items-center gap-1.5 sm:min-w-0">
        @foreach ($tabs as $tab)
            <a
                data-pan="home-tabs-{{ str($tab['label'])->lower() }}"
                href="{{ route($tab['route']) }}"
                class="{{ $tab['active'] ? 'bg-[#24314a] px-4 py-2 text-white' : 'px-2 py-2 text-slate-400 hover:text-white' }} inline-flex items-center justify-center whitespace-nowrap text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                title="{{ $tab['label'] }}"
                wire:navigate
                wire:transition
            >
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
