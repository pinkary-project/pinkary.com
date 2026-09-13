@props([
    'count',
    'limit' => 1000,
])

<div
    class="flex h-9 min-w-9 items-center justify-center"
    x-bind:aria-label="({{ $count }} > {{ $limit }} ? Math.abs({{ $limit }} - {{ $count }}) + ' characters over the limit' : {{ $count }} + ' of {{ $limit }} characters used')"
    role="status"
>
    <span
        x-cloak
        x-show="{{ $count }} > {{ $limit }}"
        class="flex h-9 min-w-9 items-center justify-center rounded-full border-2 border-red-500 px-1 text-xs font-semibold text-red-500 tabular-nums dark:border-red-400 dark:text-red-400"
        x-text="{{ $limit }} - {{ $count }}"
    ></span>

    <svg
        x-show="{{ $count }} > 0 && {{ $count }} <= {{ $limit }}"
        class="size-8 -rotate-90"
        viewBox="0 0 32 32"
        aria-hidden="true"
    >
        <circle class="stroke-slate-200 dark:stroke-slate-700" cx="16" cy="16" fill="none" r="11" stroke-width="3" />
        <circle
            class="stroke-pink-500 transition-[stroke-dashoffset] duration-150 dark:stroke-pink-400"
            cx="16"
            cy="16"
            fill="none"
            r="11"
            stroke-linecap="round"
            stroke-width="3"
            stroke-dasharray="69.12"
            x-bind:stroke-dashoffset="69.12 - (Math.min({{ $count }} / {{ $limit }}, 1) * 69.12)"
        />
    </svg>
</div>
