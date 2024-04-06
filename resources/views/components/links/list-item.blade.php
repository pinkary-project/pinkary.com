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
    class="h-12 flex-1 items-center justify-center overflow-hidden px-4 font-bold text-white transition duration-300 ease-in-out"
>
    <div class="flex h-full items-center justify-center gap-2">
        <p class="truncate">
            {{ $link->description }}
        </p>
        @if($isUserProfileOwner)
            <div class="min-w-fit flex items-center gap-1 px-2 py-1 text-xs {{ $user->link_shape }} from-slate-800 to-slate-950 flex bg-gradient-to-r shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                </svg>
                {{ $link->click_count }} {{ \Illuminate\Support\Str::plural('time', $link->click_count) }}
            </div>
        @endif
    </div>
</a>
