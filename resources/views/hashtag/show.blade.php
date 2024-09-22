<x-app-layout>
    <x-slot name="title">
        Recent posts with
        <span class="text-blue-500">#{{ $hashtag }}</span>
    </x-slot>

    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-md overflow-hidden rounded-lg px-2 sm:px-0 dark:shadow-md">
            <x-home-menu></x-home-menu>

            <livewire:home.feed :hashtag="$hashtag" />
        </div>
    </div>
</x-app-layout>
