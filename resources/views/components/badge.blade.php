@php
    $classes = "inline-block px-2 py-1 leading-none bg-slate-900 text-xs text-slate-50 rounded-full shadow-md";
@endphp

<span
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</span>
