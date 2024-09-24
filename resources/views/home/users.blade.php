<x-app-layout>
    <div class="flex flex-col items-center justify-center">
        <div class="min-h-screen w-full max-w-md overflow-hidden rounded-lg px-2 sm:px-0 dark:shadow-md">
            <x-home-menu></x-home-menu>

            <livewire:home.users :focus-input="true" />
        </div>
    </div>
</x-app-layout>
