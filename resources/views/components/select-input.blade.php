@props(['options' => [], 'disabled' => false])

<select
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'text-white caret-white focus:border-gray-500 bg-gray-800 focus:ring-gray-500 rounded-sm shadow-sm']) !!}
>
    @foreach ($options as $value => $label)
        <option value="{{ $value }}" {{ $value == $attributes->get('value') ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>
