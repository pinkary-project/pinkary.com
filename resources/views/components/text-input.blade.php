@props(['disabled' => false])

<input @disabled($disabled) {!! $attributes->merge([
    'class' =>
        'text-black caret-black focus:border-pink-500 border-slate-300  bg-slate-50/20 backdrop-blur-sm  focus:ring-slate-100 rounded-lg shadow-sm sm:text-sm',
]) !!} />
