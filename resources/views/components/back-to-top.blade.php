@props([
    'offset' => '800',
])

<div
    x-cloak
    x-data="{ show: false, offset: {{ $offset }} }"
    x-on:scroll.window="show = window.pageYOffset >= offset"
    class="fixed bottom-8 right-8"
>
    <button
        x-show="show"
        x-transition.duration.500ms
        x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="rounded-full bg-pink-500 p-2 shadow-lg"
    >
        <x-icons.arrow-top class="dark:text-black text-white" width="20" height="20"/>
    </button>
</div>
