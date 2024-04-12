<x-app-layout>
    <x-slot name="title">Trending</x-slot>

    <div class="flex flex-col items-center justify-center">
        <div class="min-h-screen w-full max-w-md overflow-hidden shadow-md">

            <x-explore-menu></x-explore-menu>

            <livewire:explore.trending :focus-input="true" />
        </div>
    </div>
</x-app-layout>
