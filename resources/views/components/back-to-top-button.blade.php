@props([
    'offset' => '800',
])

<div
    x-data="{ show: false, offset: {{ $offset }} }"
    x-on:scroll.window="show = window.pageYOffset >= offset"
    class="fixed bottom-8 right-8"
    >
    <button
        x-show="show"
        x-transition
        x-on:click="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="shadow-lg bg-pink-500 p-2 rounded-full"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
        <path fill="currentColor" d="m11 7.825l-4.9 4.9q-.3.3-.7.288t-.7-.313q-.275-.3-.288-.7t.288-.7l6.6-6.6q.15-.15.325-.212T12 4.425q.2 0 .375.063t.325.212l6.6 6.6q.275.275.275.688t-.275.712q-.3.3-.713.3t-.712-.3L13 7.825V19q0 .425-.288.713T12 20q-.425 0-.713-.288T11 19V7.825Z"/>
        </svg>
    </button>
</div>