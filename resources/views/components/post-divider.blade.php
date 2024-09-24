@props([
    'link' => null,
    'text' => null,
])

@if ($link !== null && $text !== null)
    <div class="relative -mb-3 flex h-10 items-center">
        <span
            class="absolute left-8 top-0 h-2 border-2 border-slate-400 dark:border-slate-600"
            aria-hidden="true"
        ></span>
        <span
            class="absolute left-8 h-6 border-2 border-dotted border-slate-400 dark:border-slate-600"
            aria-hidden="true"
        ></span>
        <span
            class="absolute bottom-0 left-8 h-2 border-2 border-slate-400 dark:border-slate-600"
            aria-hidden="true"
        ></span>
        <a href="{{ $link }}" class="ml-12 text-sm text-pink-500">
            {{ $text }}
        </a>
    </div>
@else
    <div class="relative -mb-3 flex h-6 items-center">
        <span class="absolute left-8 h-full w-1 bg-slate-300 dark:bg-slate-700" aria-hidden="true"></span>
    </div>
@endif
