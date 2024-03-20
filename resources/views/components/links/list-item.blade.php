@props([
    'user' => null,
    'link' => null,
])

@php
    $isUserProfileOwner = auth()->user()?->is($user);
@endphp

<a
    href="{{ $link->url }}"
    target="_blank"
    @class([
        'h-12 flex-1 items-center overflow-hidden px-4 font-bold transition duration-300 ease-in-out',
        'justify-center' => ! $isUserProfileOwner,
        'text-white',
    ])
>
    <div
        @class([
            'flex h-full items-center',
            'justify-center' => ! $isUserProfileOwner,
        ])
    >
        <p class="truncate">
            {{ $link->description }}
        </p>
    </div>
</a>
