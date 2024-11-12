<x-app-layout>
    <header class="flex items-center justify-between border-b border-white/5 p-6 xl:px-8">
        <h1 class="font-medium text-white text-base/7">Feed</h1>

        <div class="text-sm space-x-1">
            <a href="{{ route('home.trending') }}"
               class="{{ request()->routeIs('home.trending') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800/30 text-gray-400' }} bg-gray-800 rounded-md px-2.5 py-1.5">Trending</a>
            <a href="{{ route('home.following') }}"
               class="{{ request()->routeIs('home.following') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800/30 text-gray-400' }} bg-gray-800 rounded-md px-2.5 py-1.5">Following</a>
            <a href="{{ route('home.feed') }}"
               class="{{ request()->routeIs('home.feed') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800/30 text-gray-400' }} bg-gray-800 rounded-md px-2.5 py-1.5">Recent</a>
        </div>
    </header>

    @auth
        <livewire:questions.create :toId="auth()->id()"/>
    @endauth

    <livewire:home.feed />
</x-app-layout>
