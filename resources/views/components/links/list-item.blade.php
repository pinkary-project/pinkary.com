@props([
    'user' => null,
    'link' => null,
])

@php
    $isUserProfileOwner = auth()->user()?->is($user);
    $isFromOurDomain = str($link->url)->startsWith(config('app.url'));
@endphp
<a
    href="{{ $link->url }}"
    @if($isFromOurDomain) wire:navigate @else target="_blank" @endif
    rel="me noopener"
    class="h-12 flex-1 items-center justify-center overflow-hidden px-4 font-bold text-white transition duration-300 ease-in-out"
>
    <div class="flex h-full items-center justify-center">
        <p class="truncate">
            {{ $link->description }}
        </p>
    </div>
</a>
