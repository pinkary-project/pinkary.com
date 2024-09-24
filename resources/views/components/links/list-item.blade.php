@props([
    'user' => null,
    'link' => null,
])

@php
    $isUserProfileOwner = auth()
        ->user()
        ?->is($user);
    $linkWithRef = $link->url . (str_contains($link->url, '?') ? '&' : '?') . 'ref=pinkary';
@endphp

<a
    href="{{ $linkWithRef }}"
    target="_blank"
    rel="me noopener"
    class="w-full items-center justify-center px-4 font-bold text-white transition duration-300 ease-in-out"
    onclick="e.preventDefault(); window.open('{{ $linkWithRef }}', '_blank')"
>
    <div class="flex h-full items-center justify-center">
        <p class="truncate">
            {{ $link->description }}
        </p>
    </div>
</a>
