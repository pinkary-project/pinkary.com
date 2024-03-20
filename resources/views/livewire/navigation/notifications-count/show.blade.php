<div>
    @if ($count > 0)
        <span class="bg-{{ auth()->user()->right_color }} ml-1 rounded-full px-2 py-1 text-xs font-semibold text-white">
            {{ $count > 20 ? '20+' : $count }}
        </span>
    @endif
</div>
