<x-app-layout>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full overflow-hidden shadow-md sm:rounded-lg sm:max-w-md sm:px-0">
            <x-home-menu></x-home-menu>

            @auth
                <livewire:questions.create :toId="auth()->id()" />
            @endauth

            <livewire:home.feed />
        </div>
    </div>
</x-app-layout>
