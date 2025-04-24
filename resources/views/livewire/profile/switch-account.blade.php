<div class="space-y-1">
    @foreach ($accounts as $account)
        @if ($account->is(auth()->user()))
            <x-dropdown-button class="dark:bg-slate-800 bg-slate-200">
                {{ '@' . $account->username }}
            </x-dropdown-button>
        @else
            <x-dropdown-button wire:click="switch('{{ $account->username }}')">
                {{ '@' . $account->username }}
            </x-dropdown-button>
        @endif
    @endforeach
    @session('username')
        <x-dropdown-button class="dark:bg-slate-800 bg-slate-200">
            {{ '@' . $value }}
        </x-dropdown-button>
    @endsession
</div>
