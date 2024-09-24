@php
    $classes = 'inline-block rounded-full bg-slate-900 px-2 py-1 text-xs leading-none text-slate-50 shadow-md';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
