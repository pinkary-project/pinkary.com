@props(['autocomplete' => false, 'id' => null])
@php
    $componentId = $id ?? Str::uuid();
@endphp
<textarea
    {!! $attributes->merge(['class' => 'w-full border dark:border-white/10 dark:bg-white/5 bg-slate-50/50 dark:text-white text-black dark:caret-white caret-black border-slate-300 rounded-lg shadow-sm text-sm shadow-sm transition py-1.5 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20']) !!}
    @if ($autocomplete === true)
        x-data="usesDynamicAutocomplete('{{ $componentId }}')"
        x-bind="autocompleteInputBindings"
    @endif
>
    {{ $slot }}
</textarea>

@if ($autocomplete === true)
<livewire:autocomplete :componentId="$componentId"></livewire:autocomplete>
@endif
