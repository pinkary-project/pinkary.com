@props([
    'id',
    'isFollower' => false,
    'isFollowing' => false,
])

@if(auth()->id() !== $id)
    <div {{ $attributes }}
        x-data="followButton({{ $id }}, @js($isFollowing), @js($isFollower), @js(auth()->check()))"
    >
        <x-secondary-button
            wire:loading.attr="disabled"
            data-navigate-ignore="true"
            type="button"
            x-on:click="toggleFollow()"
            class="text-xs md:text-sm text-nowrap"
        >
            <span x-text='buttonText' :title='buttonText' ></span>
        </x-secondary-button>
    </div>
@endif
