<x-app-layout>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-md overflow-hidden rounded-lg px-2 sm:px-0 dark:shadow-md">
            <x-home-menu></x-home-menu>

            <livewire:home.trending-questions :focus-input="true" />
        </div>
    </div>
</x-app-layout>
