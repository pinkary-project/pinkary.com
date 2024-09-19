@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'dark:bg-slate-900 bg-slate-100 dark:border-none border border-slate-100 py-1 text-slate-500', 'dropdownClasses' => ''])

@php
    switch ($align) {
        case 'left':
            $alignmentClasses = 'start-0 ltr:origin-top-left rtl:origin-top-right';
            break;
        case 'top':
            $alignmentClasses = 'origin-top';
            break;
        case 'right':
        default:
            $alignmentClasses = 'end-0 ltr:origin-top-right rtl:origin-top-left';
            break;
    }

    switch ($width) {
        case '48':
            $width = 'w-48';
            break;
    }
@endphp

<div
    class="relative"
    x-data="{ open: false }"
    x-on:click.outside="open = false"
    @close.stop="open = false"
>
    <div x-on:click="open = ! open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition duration-75 ease-in"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="{{ $width }} {{ $alignmentClasses }} {{ $dropdownClasses }} absolute z-50 mt-2 rounded-md shadow-lg"
        style="display: none"
        x-on:click="open = false"
    >
        <div class="{{ $contentClasses }} rounded-md ring-1 ring-black ring-opacity-5">
            {{ $content }}
        </div>
    </div>
</div>
