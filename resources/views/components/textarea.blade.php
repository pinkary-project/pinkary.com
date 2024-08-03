@props(['autocomplete' => false])
<textarea
    {!! $attributes->merge(['class' => 'w-full text-white caret-white focus:border-pink-500 border-slate-800 bg-slate-900/50 backdrop-blur-sm focus:ring-slate-900 rounded-lg shadow-sm sm:text-sm']) !!}
    @if ($autocomplete === true)
        x-data="usesDynamicAutocomplete"
        x-bind="autocompleteInputBindings"
    @endif
>
    {{ $slot }}
</textarea>

@if ($autocomplete === true)
<livewire:autocomplete></livewire:autocomplete>
@endif
