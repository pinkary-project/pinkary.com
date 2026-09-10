@props([
    'classes' => 'flex size-9 items-center justify-center rounded-xl border border-slate-200/70 bg-white/90 text-slate-600 shadow-xs backdrop-blur transition hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800/40 dark:bg-[#050c1d]/90 dark:text-slate-300 dark:hover:bg-[#11192b] dark:hover:text-white',
    'iconClasses' => 'size-5',
])

<button
    x-data="themeSwitch()"
    type="button"
    @click="toggle()"
    {{ $attributes->merge(['class' => $classes]) }}
    title="{{ __('Toggle theme') }}"
    aria-label="{{ __('Toggle theme') }}"
>
    <x-heroicon-o-sun class="{{ $iconClasses }} text-amber-500 block dark:hidden" />
    <x-heroicon-o-moon class="{{ $iconClasses }} text-pink-400 hidden dark:block" />
</button>
