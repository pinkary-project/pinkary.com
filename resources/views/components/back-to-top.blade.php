@props([
    'offset' => '800',
])

<div
    x-cloak
    x-data="{ show: false, offset: {{ $offset }} }"
    x-on:scroll.window="show = window.pageYOffset >= offset"
    class="fixed bottom-16 sm:bottom-8 right-2 sm:right-8"
>
    <button
        x-show="show"
        x-transition.duration.500ms
        x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="p-2 bg-pink-500 rounded-full shadow-lg"
    >
        <x-icons.arrow-top width="20" height="20"/>
    </button>
</div>
