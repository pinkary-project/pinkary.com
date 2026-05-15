@props(['label', 'icon', 'route', 'key'])
<a
    data-pan="home-tabs-{{ $key }}"
    href="{{ route($route) }}"
    @class([
        'inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border dark:border-transparent border-slate-200 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none',
        'bg-pink-600 text-slate-100' => request()->routeIs($route),
        'dark:text-slate-500 text-slate-400 dark:hover:text-slate-100 hover:text-slate-800 dark:bg-slate-900 bg-slate-50' => ! request()->routeIs($route),
    ])
    title="{{ $label }}"
    wire:navigate
    wire:transition
>
    @if($icon)
        <x-dynamic-component :component="$icon" class="h-6 w-6 xsm:mr-2" />
    @endif
    <span class="hidden xsm:inline">{{ $label }}</span>
</a>
