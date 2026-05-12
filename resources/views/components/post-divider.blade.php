@props([
    'link' => null,
    'text' => null,
])

@if ($link !== null && $text !== null)
    <div class="relative h-8 -mb-2 flex items-center">
        <span class="absolute left-8 h-2 top-0 border-2 dark:border-slate-600 border-slate-400" aria-hidden="true"></span>
        <span class="absolute left-8 h-6 border-2 dark:border-slate-600 border-slate-400 border-dotted" aria-hidden="true"></span>
        <span class="absolute left-8 h-2 bottom-0 border-2 dark:border-slate-600 border-slate-400 " aria-hidden="true"></span>
        <a href="{{ $link }}" class="text-sm text-pink-500 ml-12">
            {{ $text }}
        </a>
    </div>
@else
    <div class="relative h-4 -mb-1 flex items-center">
        <span class="absolute left-8 h-full w-px bg-slate-700/55" aria-hidden="true"></span>
    </div>
@endif

