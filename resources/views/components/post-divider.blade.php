@props([
    'link' => null,
    'text' => null,
])

@if ($link !== null && $text !== null)
    <div class="relative h-10 -mb-1 flex items-center">
        <span class="absolute left-5 sm:left-6 h-2 top-0 border-2 dark:border-slate-600 border-slate-400" aria-hidden="true"></span>
            <span class="absolute left-5 sm:left-6 h-10 border-2 dark:border-slate-600 border-slate-400 border-dotted" aria-hidden="true"></span>
            <span class="absolute left-5 sm:left-6 h-2 bottom-0 border-2 dark:border-slate-600 border-slate-400 " aria-hidden="true"></span>
        <a href="{{ $link }}" class="text-sm text-pink-500 ml-10">
            {{ $text }}
        </a>
    </div>
@else
    <div class="relative h-2 flex items-center">
        <span class="absolute left-5 sm:left-6 h-full w-0.5 bg-slate-200 dark:bg-slate-700" aria-hidden="true"></span>
    </div>
@endif

