@props([
    'classes' => '',
    'dropdown' => false,
])

@if ($dropdown)
    <x-dropdown-link :href="route('changelog')"> {{ __('Changelog') }} </x-dropdown-link>
@else
    <a title="Changelog" href="{{ route('changelog') }}" class="{{ $classes }}" wire:navigate>
        <x-heroicon-o-document-text class="h-5 w-5" />
        <span>{{ __('Changelog') }}</span>
    </a>
@endif
