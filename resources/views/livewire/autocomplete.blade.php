<div
    x-data="dynamicAutocomplete({ types: @js($this->autocompleteTypes) })"
    x-cloak
    x-show="showAutocompleteOptions"
>
    <div class="fixed left-0 top-0 h-full w-full" @click="closeResults()" aria-hidden="true"></div>
    <div
        x-show="showAutocompleteOptions"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave-end="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
        class="absolute left-0 z-50 max-h-96 w-full max-w-sm translate-x-2 overflow-auto rounded-lg bg-slate-50 p-2 text-black shadow-md shadow-slate-200 focus:outline-none sm:text-sm dark:bg-slate-950 dark:text-white dark:shadow-slate-800"
    >
        <ul x-ref="results" class="relative space-y-2">
            @forelse ($this->autocompleteResults as $index => $result)
                @php
                    /** @var \App\Services\Autocomplete\Result $result */
                @endphp

                @if ($result->view)
                    <li
                        class="relative m-0 cursor-pointer select-none rounded-lg p-2 text-black hover:bg-slate-100 dark:text-white dark:hover:bg-slate-800"
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
