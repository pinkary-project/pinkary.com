<div
    x-data="dynamicAutocomplete({ types: @js($this->autocompleteTypes), componentId: '{{ $componentId }}' })"
    x-cloak
    x-show="showAutocompleteOptions"
>
    <div class="fixed top-0 left-0 h-full w-full" @click="closeResults()" aria-hidden="true"></div>
    <div
        x-show="showAutocompleteOptions"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="absolute left-0 z-50 max-h-96 w-full max-w-sm translate-x-2 overflow-auto rounded-lg bg-slate-50 p-2 text-black shadow-md shadow-slate-200 focus:outline-none sm:text-sm dark:bg-slate-950 dark:text-white dark:shadow-slate-800"
    >
        <ul x-ref="results" class="relative space-y-2">
            @forelse ($this->autocompleteResults as $index => $result)
                @php /** @var \App\Services\Autocomplete\Result $result */ @endphp
                @if ($result->view)
                    <li
                        class="relative m-0 cursor-pointer rounded-lg p-2 text-black select-none hover:bg-slate-100 dark:text-white dark:hover:bg-slate-800"
                        x-bind:data-id="{{ $result->id }}"
                        x-bind:data-replacement="'{{ $result->replacement }}'"
                        @click="select('{{ $result->replacement }}')"
                        :class="{ 'dark:bg-slate-800 bg-slate-50': selectedIndex === {{ $index }} }"
                        wire:key="{{ $result->id }}"
                        role="option"
                    >
                        @include($result->view)
                    </li>
                @endif
            @empty
                <li wire:loading.remove class="p-2 text-slate-600 dark:text-slate-400" wire:key="no-results">
                    No matching results...
                </li>
                <li wire:loading class="p-2 text-slate-600 dark:text-slate-400" wire:key="no-results-loading">
                    Searching...
                </li>
            @endforelse
        </ul>
    </div>
</div>
