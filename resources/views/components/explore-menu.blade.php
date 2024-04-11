<div class="mb-8 flex justify-between space-x-4">
    <a href="{{ route('explore.users') }}" class="{{ request()->routeIs('explore.users') ? 'text-slate-100 bg-pink-600' : 'text-slate-500 hover:text-slate-100' }} inline-flex flex-1 items-center justify-center rounded-md border border-transparent bg-slate-900 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none" wire:navigate>
        <x-icons.users class="h-6 w-6 mr-3" /> {{ __('Users') }}
    </a>
    <a href="{{ route('explore.for_you') }}" class="{{ request()->routeIs('explore.for_you') ? 'text-slate-100 bg-pink-600' : 'text-slate-500 hover:text-slate-100' }} inline-flex flex-1 items-center justify-center rounded-md border border-transparent bg-slate-900 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none" wire:navigate>
        <x-icons.smile class="h-6 w-6 mr-3" /> {{ __('For you') }}
    </a>
    <a href="{{ route('explore.trending') }}" class="{{ request()->routeIs('explore.trending') ? 'text-slate-100 bg-pink-600' : 'text-slate-500 hover:text-slate-100' }} inline-flex flex-1 items-center justify-center rounded-md border border-transparent bg-slate-900 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none" wire:navigate>
        <x-icons.trending-solid color="currentColor" class="h-6 w-6 mr-3" /> {{ __('Trending') }}
    </a>
</div>
