@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'text-white caret-white focus:border-pink-500 border-slate-800 bg-slate-900/50 backdrop-blur-sm focus:ring-slate-900 rounded-lg shadow-sm sm:text-sm']) !!}
/>
