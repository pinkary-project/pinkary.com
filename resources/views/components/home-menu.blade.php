@props([
    'currentSection' => '',
    'sections' => [],
])

<div class="mb-8 flex justify-between space-x-2">
    @foreach($sections as $section => ['label' => $label, 'icon' => $icon])
        <a
            href="{{ route('home.explorer', $section) }}"
            @class([
              'text-slate-500 hover:text-slate-100 bg-slate-900',
              'inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none',
              '!bg-pink-600 !text-slate-100' => $currentSection === $section,
            ])
            wire:navigate
        >
            <x-dynamic-component :component="$icon" class="h-6 w-6 xsm:mr-3" />
            <span class="hidden xsm:inline">{{ $label }}</span>
        </a>
    @endforeach
</div>
