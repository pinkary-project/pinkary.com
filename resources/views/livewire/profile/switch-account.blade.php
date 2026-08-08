<div class="space-y-1">
    @foreach ($accounts as $account)
        @if ($account->is(auth()->user()))
            <x-dropdown-button class="bg-slate-200 dark:bg-slate-800" disabled>
                {{ '@' . $account->username }}
            </x-dropdown-button>
        @else
            <x-dropdown-button wire:click="switch('{{ $account->username }}')">
                {{ '@' . $account->username }}
            </x-dropdown-button>
        @endif
    @endforeach
</div>
