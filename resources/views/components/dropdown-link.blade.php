<a
    {{ $attributes->merge(['class' => 'block w-full rounded-xl px-4 py-2.5 text-start text-sm leading-5 text-slate-300 transition duration-150 ease-in-out hover:bg-[#11192b] hover:text-white']) }}
    wire:navigate
>
    {{ $slot }}
</a>
