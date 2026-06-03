@props([
    'link' => null,
    'text' => null,
])

@if ($link !== null && $text !== null)
    <div class="-my-3 flex min-h-12 items-stretch gap-3">
        <div class="flex w-10 shrink-0 flex-col items-center sm:w-12">
            <div class="w-0 flex-1 border-l border-dotted border-slate-300 dark:border-slate-600" aria-hidden="true"></div>
        </div>
        <a href="{{ $link }}" class="flex items-center text-sm font-medium text-pink-500 transition-colors hover:text-pink-400">
            {{ $text }}
        </a>
    </div>
@else
    <div class="-my-3 flex h-8 shrink-0 items-stretch gap-3">
        <div class="flex w-10 shrink-0 flex-col items-center sm:w-12">
            <div class="w-0.5 flex-1 bg-slate-300 dark:bg-slate-600" aria-hidden="true"></div>
        </div>
    </div>
@endif

