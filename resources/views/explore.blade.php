<x-app-layout>
    <x-slot name="title">Explore</x-slot>

    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-md overflow-hidden rounded-lg shadow-md">
            <livewire:users.index focus-input="true" />
        </div>
    </div>
</x-app-layout>
