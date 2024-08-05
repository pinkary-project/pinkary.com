@if ($perPage < 100 && $paginator->hasMorePages())
    <div x-intersect.margin.600px="$wire.loadMore()"></div>
@elseif ($perPage > 10)
    <div class="text-center text-slate-400">{{ $message }}</div>
@endif
