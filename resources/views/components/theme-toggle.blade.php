@props([
    'classes' => 'rounded-2xl border border-slate-200/70 bg-slate-50 p-1.5 dark:border-slate-800/40 dark:bg-[#0b1324]',
    'buttonClasses' => 'flex flex-1 items-center justify-center rounded-xl border border-slate-200/70 px-3 py-1.5 text-slate-500 transition dark:border-slate-800/50 dark:text-slate-300',
])

<div x-data="themeSwitch()" {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex w-full flex-row justify-between gap-1.5">
        <button
            type="button"
            class="{{ $buttonClasses }}"
            x-bind:class="
                theme == 'light'
                    ? 'border-pink-500 bg-pink-500 text-white'
                    : 'hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#11192b] dark:hover:text-white'
            "
            @click="setTheme('light')"
            title="{{ __('Use light theme') }}"
            aria-label="{{ __('Use light theme') }}"
        >
            <x-heroicon-o-sun class="h-4 w-4" />
        </button>
        <button
            type="button"
            class="{{ $buttonClasses }}"
            x-bind:class="
                theme == 'dark'
                    ? 'border-pink-500 bg-pink-500 text-white'
                    : 'hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#11192b] dark:hover:text-white'
            "
            @click="setTheme('dark')"
            title="{{ __('Use dark theme') }}"
            aria-label="{{ __('Use dark theme') }}"
        >
            <x-heroicon-o-moon class="h-4 w-4" />
        </button>
        <button
            type="button"
            class="{{ $buttonClasses }}"
            x-bind:class="
                theme == 'system'
                    ? 'border-pink-500 bg-pink-500 text-white'
                    : 'hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#11192b] dark:hover:text-white'
            "
            @click="setTheme('system')"
            title="{{ __('Use system theme') }}"
            aria-label="{{ __('Use system theme') }}"
        >
            <x-heroicon-o-computer-desktop class="h-4 w-4" />
        </button>
    </div>
</div>
