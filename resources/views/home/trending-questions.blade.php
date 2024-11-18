<x-app-layout>
    <header class="sticky top-16 border-b border-white/5 bg-gray-900/50 backdrop-blur-lg">
        <div class="flex items-center justify-between p-6 xl:px-8">
            <h1 class="font-medium text-white text-base/7">Feed</h1>
            <x-home-menu />
        </div>
    </header>

    @auth
        <div class="border-b border-white/5">
            <livewire:questions.create :toId="auth()->id()"/>
        </div>
    @endauth

    <livewire:home.trending-questions :focus-input="true" />
</x-app-layout>
