<div class="mb-8 flex justify-between space-x-2">
    <a
        href="{{ route('home.feed') }}"
        class="{{ request()->routeIs('home.feed') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 dark:hover:text-slate-100 hover:text-slate-800 dark:bg-slate-900 bg-slate-50 ' }} inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border dark:border-transparent border-slate-200 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        title="{{ __('Feed') }}"
        wire:navigate
        wire:transition
    >
        <x-heroicon-o-home
            class="h-6 w-6 xsm:mr-2" />
        <span class="hidden xsm:inline">{{ __('Feed') }}</span>
    </a>

    <a
        href="{{ route('home.for_you') }}"
        class="{{ request()->routeIs('home.for_you') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 dark:hover:text-slate-100 hover:text-slate-800 dark:bg-slate-900 bg-slate-50 ' }} inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border dark:border-transparent border-slate-200 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        title="{{ __('For you') }}"
        wire:navigate
        wire:transition
    >
        <x-heroicon-o-heart
            class="h-6 w-6 xsm:mr-2" />
        <span class="hidden xsm:inline">{{ __('For you') }}</span>
    </a>

    <a
        href="{{ route('home.trending') }}"
        class="{{ request()->routeIs('home.trending') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 dark:hover:text-slate-100 hover:text-slate-800 dark:bg-slate-900 bg-slate-50 ' }} inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border dark:border-transparent border-slate-200 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        title="{{ __('Trending') }}"
        wire:navigate
        wire:transition
    >
        <x-heroicon-m-fire
            color="currentColor"
            class="h-6 w-6 xsm:mr-2"
        />
        <span class="hidden xsm:inline">{{ __('Trending') }}</span>
    </a>

    <a
        href="{{ route('home.users') }}"
        class="{{ request()->routeIs('home.users') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 dark:hover:text-slate-100 hover:text-slate-800 dark:bg-slate-900 bg-slate-50 ' }} inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border dark:border-transparent border-slate-200 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        title="{{ __('Search') }}"
        wire:navigate
        wire:transition
    >
        <x-heroicon-o-magnifying-glass class="h-6 w-6 xsm:mr-2" />
        <span class="hidden xsm:inline">{{ __('Search') }}</span>
    </a>
</div>
