<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" @close.stop="open = false">
    <div x-on:click="open = ! open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="translate-y-1 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition duration-75 ease-in"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-1 opacity-0"
        class="absolute z-50 mt-2 rounded-md"
        style="display: none"
        x-on:click="open = false"
    >
        <div class="flex flex-col space-y-2">
            {{ $content }}
        </div>
    </div>
</div>
