<x-app-layout>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full overflow-hidden shadow-md sm:max-w-md sm:rounded-lg">
            <x-home-menu></x-home-menu>

            <livewire:home.trending-questions :focus-input="true" />
        </div>
    </div>
</x-app-layout>
