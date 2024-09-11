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
    rel="me noopener"
    class="items-center justify-center px-4 font-bold text-white transition duration-300 ease-in-out"
>
    <div class="flex h-full items-center justify-center">

        @php
            $social = App\Enums\Social::getSocialFromUrl($link->url);
        @endphp

        @if($social!== null)
            <span class="[&>svg]:h-5 [&>svg]:w-5 mr-2">
                @includeIf('components.icons.socials.' . $social->value)
            </span>
        @endif

        <p class="truncate">
            {{ $link->description }}
        </p>
    </div>
</a>
