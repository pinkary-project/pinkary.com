<div class="mb-8 flex justify-center">
    <a href="{{ route('explore.users') }}" class="mr-2" wire:navigate>
        <button
            type="button"
            class="{{ request()->routeIs('explore.users') ? 'text-slate-100' : 'text-slate-500 hover:text-slate-100' }} inline-flex items-center rounded-md border border-transparent bg-slate-900 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        >
            <x-icons.users class="h-6 w-6 mr-3" /> {{ __('Users') }}
        </button>
    </a>
    <a href="{{ route('explore.for_you') }}" class="mr-2" wire:navigate>
        <button
            type="button"
            class="{{ request()->routeIs('explore.for_you') ? 'text-slate-100' : 'text-slate-500 hover:text-slate-100' }} inline-flex items-center rounded-md border border-transparent bg-slate-900 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        >
            <x-icons.smile class="h-6 w-6 mr-3" /> {{ __('For you') }}


        </button>
    </a>
    <a href="{{ route('explore.trending') }}" class="mr-2" wire:navigate>
        <button
            type="button"
            class="{{ request()->routeIs('explore.trending') ? 'text-slate-100' : 'text-slate-500 hover:text-slate-100' }} inline-flex items-center rounded-md border border-transparent bg-slate-900 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
        >
            <x-icons.fire-solid color="currentColor" class="h-6 w-6 mr-3" /> {{ __('Trending') }}

        </button>
    </a>
</div>
