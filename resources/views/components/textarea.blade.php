@props(['autocomplete' => false, 'id' => null])
@php
    $componentId = $id ?? Str::uuid();
@endphp
<textarea {!! $attributes->merge([
    'class' =>
        'w-full text-black caret-black focus:border-pink-500 border-slate-300  bg-slate-50/50 backdrop-blur-sm  focus:ring-slate-100 rounded-lg shadow-sm sm:text-sm',
]) !!}
    @if ($autocomplete === true) x-data="usesDynamicAutocomplete('{{ $componentId }}')"
        x-bind="autocompleteInputBindings" @endif>
    {{ $slot }}
</textarea>

@if ($autocomplete === true)
    <livewire:autocomplete :componentId="$componentId"></livewire:autocomplete>
@endif
