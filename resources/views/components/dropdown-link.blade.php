<a
    {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 text-slate-400 hover:bg-slate-800 transition duration-150 ease-in-out']) }}
    wire:navigate
>
    {{ $slot }}
</a>
