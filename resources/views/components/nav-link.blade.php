@props([
    'active',
])

@php
    $classes =
        $active ?? false
            ? 'inline-flex items-center border-b-2 border-indigo-400 px-1 pt-1 text-sm font-medium leading-5 text-slate-900 transition duration-150 ease-in-out focus:border-indigo-700 focus:outline-none'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-slate-500 transition duration-150 ease-in-out hover:border-slate-300 hover:text-slate-700 focus:border-slate-300 focus:text-slate-700 focus:outline-none';
@endphp

<a
    {{ $attributes->merge(['class' => $classes]) }}
    wire:navigate
>
    {{ $slot }}
</a>
