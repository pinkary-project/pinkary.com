@if ($question->pinned && $pinnable)
    <div class="mb-2 flex items-center space-x-1 px-4 text-sm focus:outline-none">
        <x-icons.pin class="h-4 w-4 text-slate-400" />
        <span class="text-slate-400">Pinned</span>
    </div>
@endif
