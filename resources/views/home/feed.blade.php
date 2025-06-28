<x-app-layout>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-md overflow-hidden rounded-lg px-2 sm:px-0">
            <div class="pb-2  bg-slate-100 z-40 backdrop-blur-2xl h-fit">
                <x-home-menu></x-home-menu>
            </div>
            <div>
                @auth
                    <livewire:questions.create :toId="auth()->id()" />
                @endauth
                <livewire:home.feed />
            </div>
        </div>
    </div>
</x-app-layout>
