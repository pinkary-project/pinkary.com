<li {{ $attributes->merge(['class' => 'group']) }}>
    <a href="{{ $href }}"
       wire:navigate
       wire:transition
       class="{{ $active ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} group w-full flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold">
        {{ $slot }}
    </a>
</li>
