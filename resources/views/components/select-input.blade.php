@props(['options' => [], 'disabled' => false])

<select
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'text-white sm:text-sm focus:border-pink-500 border-slate-800 bg-slate-900/50 backdrop-blur-sm focus:ring-slate-900 rounded-lg shadow-sm']) !!}
>
    @foreach ($options as $value => $label)
        <option
            value="{{ $value }}"
            {{ $value == $attributes->get('value') ? 'selected' : '' }}
        >
            {{ $label }}
        </option>
    @endforeach
</select>
