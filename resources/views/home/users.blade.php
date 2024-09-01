<x-app-layout>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full min-h-screen overflow-hidden shadow-md sm:max-w-md sm:rounded-2xl">
            <x-home-menu></x-home-menu>
            <livewire:home.users :focus-input="true" />
        </div>
    </div>
</x-app-layout>
