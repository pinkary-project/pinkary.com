@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'border border-slate-200/80 bg-white/95 py-1 text-slate-600 backdrop-blur dark:border-white/10 dark:bg-gray-900/95 dark:text-gray-300', 'dropdownClasses' => ''])

@php
    switch ($align) {
        case 'left':
            $alignmentClasses = 'inset-s-0 ltr:origin-top-left rtl:origin-top-right';
            break;
        case 'top':
            $alignmentClasses = 'origin-top';
            break;
        case 'right':
        default:
            $alignmentClasses = 'inset-e-0 ltr:origin-top-right rtl:origin-top-left';
            break;
    }

    switch ($width) {
        case '48':
            $width = 'w-48';
            break;
        case '60':
            $width = 'w-60';
            break;
    }
@endphp

<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" @close.stop="open = false">
    <div x-on:click="open = ! open">{{ $trigger }}</div>

    <div
        x-show="open"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition duration-75 ease-in"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="{{ $width }} {{ $alignmentClasses }} {{ $dropdownClasses }} absolute z-50 mt-2 rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-black/30"
        style="display: none"
        x-on:click="open = false"
    >
        <div class="{{ $contentClasses }} rounded-2xl ring-1 ring-slate-900/5 dark:ring-white/5">{{ $content }}</div>
    </div>
</div>
