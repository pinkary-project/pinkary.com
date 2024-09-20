@if ($perPage < 100 && $paginator->hasMorePages())
    <div x-intersect.margin.50%="$wire.loadMore()" class="text-center">
        <div class="text-center dark:text-slate-400 text-slate-600" wire:loading wire:target="loadMore">
            <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin" />
        </div>
    </div>
@elseif ($perPage > 10)
    <div class="text-center dark:text-slate-400 text-slate-600">{{ $message }}</div>
@endif
