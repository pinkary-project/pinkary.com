@props(['disabled' => false, 'label' => __('password'), 'wrapperClasses' => ''])

<div class="relative {{ $wrapperClasses }}" x-data="{ showPassword: false }">
    <x-text-input
        type="password"
        x-bind:type="showPassword ? 'text' : 'password'"
        {{ $attributes->class('pr-10')->merge(['disabled' => $disabled]) }}
    />

    <button
        type="button"
        x-on:click="showPassword = ! showPassword"
        x-bind:aria-label="showPassword ? 'Hide {{ $label }}' : 'Show {{ $label }}'"
        x-bind:aria-pressed="showPassword"
        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 transition hover:text-slate-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-pink-500/20 dark:text-gray-500 dark:hover:text-gray-300"
    >
        <x-icons.eye x-cloak x-show="! showPassword" class="size-5" />
        <x-icons.eye-off x-cloak x-show="showPassword" class="size-5" />
    </button>
</div>
