<x-app-layout>
    <header class="flex items-center justify-between border-b border-white/5 p-6 xl:px-8">
        <h1 class="font-medium text-white text-base/7">Feed</h1>
        <x-home-menu />
    </header>

    @auth
        <livewire:questions.create :toId="auth()->id()"/>
    @endauth

    <livewire:home.feed />
</x-app-layout>
