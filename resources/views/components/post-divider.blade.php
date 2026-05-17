@props([
    'link' => null,
    'text' => null,
])

@if ($link !== null && $text !== null)
    <div class="relative -my-3 flex min-h-12 items-center">
        <span class="absolute inset-y-0 left-5 w-0 -translate-x-1/2 border-l border-dotted border-slate-300 dark:border-slate-600 sm:left-6" aria-hidden="true"></span>
        <a href="{{ $link }}" class="ml-10 inline-flex items-center text-sm font-medium text-pink-500 transition-colors hover:text-pink-400 sm:ml-12">
            {{ $text }}
        </a>
    </div>
@else
    <div class="relative -my-3 h-100">
        <span class="absolute inset-y-0 left-5 w-0.5 -translate-x-1/2 bg-slate-300 dark:bg-slate-600 sm:left-6" aria-hidden="true"></span>
    </div>
@endif

