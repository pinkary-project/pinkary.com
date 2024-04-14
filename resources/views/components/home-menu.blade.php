<div class="mb-8 flex justify-between space-x-2">
    <a
        href="{{ route('home.feed') }}"
        class="{{ request()->routeIs('home.feed') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 hover:text-slate-100 bg-slate-900 ' }} inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        wire:navigate
        wire:transition
    >
        <x-icons.home class="h-6 w-6 xsm:mr-3" />
        <span class="hidden xsm:inline">{{ __('Feed') }}</span>
    </a>

    <a
        href="{{ route('home.for_you') }}"
        class="{{ request()->routeIs('home.for_you') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 hover:text-slate-100 bg-slate-900 ' }} inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        wire:navigate
        wire:transition
    >
        <x-icons.smile class="h-6 w-6 xsm:mr-3" />
        <span class="hidden xsm:inline">{{ __('For you') }}</span>
    </a>

    <a
        href="{{ route('home.trending') }}"
        class="{{ request()->routeIs('home.trending') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 hover:text-slate-100 bg-slate-900 ' }} inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        wire:navigate
        wire:transition
    >
        <x-icons.trending-solid color="currentColor" class="h-6 w-6 xsm:mr-3" />
        <span class="hidden xsm:inline">{{ __('Trending') }}</span>
    </a>

    <a
        href="{{ route('home.users') }}"
        class="{{ request()->routeIs('home.users') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 hover:text-slate-100 bg-slate-900 ' }} inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        wire:navigate
        wire:transition
    >
        <x-icons.users class="h-6 w-6 xsm:mr-3" />
        <span class="hidden xsm:inline">{{ __('Users') }}</span>
    </a>
</div>
