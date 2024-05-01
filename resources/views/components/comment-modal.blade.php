<div
    id="{{ $name }}"
    x-data="{ show: false }"
    x-on:keydown.escape.window="show = false;"
    @open-modal.window="$event.detail.name == '{{$name}}' ? (show = true) : null"
    @close-modal.window="$event.detail.name == '{{$name}}' ? (show = false) : null"
>
    <div x-show="show" x-cloak
         class="fixed z-30 inset-0 flex items-center justify-center bg-slate-900 bg-opacity-5 bg-clip-padding backdrop-blur-sm backdrop-filter">
        <div x-on:click.outside="show = false;"
             class="w-full max-w-md rounded-lg bg-slate-800 p-8">
            {{ $slot }}
        </div>
    </div>
</div>
