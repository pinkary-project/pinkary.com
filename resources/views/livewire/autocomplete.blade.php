<div
    x-data="dynamicAutocomplete({ types: @js($this->autocompleteTypes) })"
    x-cloak
    x-show="showAutocompleteOptions"
>
    <div
        class="fixed h-full top-0 left-0 w-full"
        @click="closeResults()"
        aria-hidden="true">
    </div>
    <div
        x-show="showAutocompleteOptions"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="absolute w-full max-h-96 overflow-auto max-w-sm z-50 focus:outline-none sm:text-sm bg-slate-950 shadow-md shadow-slate-800 rounded-lg text-white p-2 translate-x-2 left-0"
    >

        <ul
            x-ref="results"
            class="relative space-y-2"
        >
            @forelse($this->autocompleteResults as $index => $result)
                @php /** @var \App\Services\Autocomplete\Result $result */ @endphp
                @if($result->view)
                    <li
                        class="text-white rounded-lg m-0 p-2 cursor-pointer select-none relative hover:bg-slate-800"
                        x-bind:data-id="{{ $result->id }}"
                        x-bind:data-replacement="'{{ $result->replacement }}'"
                        @click="select('{{ $result->replacement }}')"
                        :class="{ 'bg-slate-800': selectedIndex === {{ $index }} }"
                        wire:key="{{ $result->id }}"
                        role="option"
                    >
                        @include($result->view)
                    </li>
                @endif
            @empty
                <li wire:loading.remove class="text-slate-400 p-2" wire:key="no-results">
                    No matching results...
                </li>
                <li wire:loading class="text-slate-400 p-2" wire:key="no-results-loading">
                    Searching...
                </li>
            @endforelse
        </ul>
    </div>
</div>
