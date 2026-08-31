@if ($perPage < 100 && $paginator->hasMorePages())
    <div x-intersect.margin.50%="$wire.loadMore()" class="text-center">
        <div class="text-center text-slate-600 dark:text-slate-400" wire:loading wire:target="loadMore">
            <x-heroicon-o-arrow-path class="h-5 w-5 animate-spin" />
        </div>
    </div>
@elseif ($perPage > 10)
    <div class="text-center text-slate-600 dark:text-slate-400">{{ $message }}</div>
@endif
