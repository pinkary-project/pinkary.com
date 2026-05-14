@props([
    'link' => null,
    'text' => null,
])

@if ($link !== null && $text !== null)
    <div class="relative flex min-h-10 items-center">
        <span class="absolute inset-y-0 left-5 w-px -translate-x-1/2 bg-slate-200 dark:bg-slate-700 sm:left-6" aria-hidden="true"></span>
        <a href="{{ $link }}" class="ml-10 inline-flex items-center text-sm font-medium text-pink-500 transition-colors hover:text-pink-400 sm:ml-12">
            {{ $text }}
        </a>
    </div>
@else
    <div class="relative h-3">
        <span class="absolute inset-y-0 left-5 w-px -translate-x-1/2 bg-slate-200 dark:bg-slate-700 sm:left-6" aria-hidden="true"></span>
    </div>
@endif

