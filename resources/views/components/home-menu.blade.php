<div class="mb-8 flex justify-between space-x-2">

    <a
        href="{{ route('home.feed') }}"
        class="{{ request()->routeIs('home.feed') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 hover:text-slate-100 bg-slate-900 ' }} whitespace-nowrap inline-flex flex-1 items-center justify-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        wire:navigate
    >
        <x-icons.home class="mr-3 h-6 w-6" />
        {{ __('Feed') }}
    </a>

    <a
        href="{{ route('home.for_you') }}"
        class="{{ request()->routeIs('home.for_you') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 hover:text-slate-100 bg-slate-900 ' }} whitespace-nowrap inline-flex flex-1 items-center justify-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        wire:navigate
    >
        <x-icons.smile class="mr-3 h-6 w-6" />
        {{ __('For you') }}
    </a>

    <a
        href="{{ route('home.trending') }}"
        class="{{ request()->routeIs('home.trending') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 hover:text-slate-100 bg-slate-900 ' }} whitespace-nowrap inline-flex flex-1 items-center justify-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        wire:navigate
    >
        <x-icons.trending-solid color="currentColor" class="mr-3 h-6 w-6" />
        {{ __('Trending') }}
    </a>

    <a
        href="{{ route('home.users') }}"
        class="{{ request()->routeIs('home.users') ? 'bg-pink-600 text-slate-100' : 'text-slate-500 hover:text-slate-100 bg-slate-900 ' }} whitespace-nowrap inline-flex flex-1 items-center justify-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        wire:navigate
    >
        <x-icons.users class="mr-3 h-6 w-6" />
        {{ __('Users') }}
    </a>
</div>
