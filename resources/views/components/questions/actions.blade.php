@if (auth()->check() && auth()->user()->can('update', $question))
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button class="inline-flex items-center rounded-md border border-transparent py-1 text-sm text-slate-400 transition duration-150 ease-in-out hover:text-slate-50 focus:outline-none">
                <x-icons.ellipsis-horizontal class="h-6 w-6" />
            </button>
        </x-slot>

        <x-slot name="content">
            @if (! $question->pinned && auth()->user()->can('pin', $question))
                <x-dropdown-button wire:click="pin" class="flex items-center gap-1.5">
                    <x-icons.pin class="h-4 w-4 text-slate-50" />
                    <span>Pin</span>
                </x-dropdown-button>
            @elseif ($question->pinned)
                <x-dropdown-button wire:click="unpin" class="flex items-center gap-1.5">
                    <x-icons.pin class="h-4 w-4" />
                    <span>Unpin</span>
                </x-dropdown-button>
            @endif
            @if (! $question->is_ignored && auth()->user()->can('ignore', $question))
                <x-dropdown-button
                    wire:click="ignore"
                    wire:confirm="Are you sure you want to delete this question?"
                    class="flex items-center gap-1.5"
                >
                    <x-icons.trash class="h-4 w-4" />
                    <span>Delete</span>
                </x-dropdown-button>
            @endif
        </x-slot>
    </x-dropdown>
@endif
