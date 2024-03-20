@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'text-white caret-white focus:border-gray-500 bg-gray-800 focus:ring-gray-500 rounded-sm shadow-sm']) !!}
/>
