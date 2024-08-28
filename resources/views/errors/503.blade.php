<x-about-layout title="{{ __('Service Unavailable') }}">
    <div class="relative flex justify-center">
        <div
            class="absolute -top-48 -z-10 size-[400px] -rotate-45 rounded-full bg-gradient-to-br from-indigo-300 via-rose-200 to-green-600 opacity-70 blur-3xl lg:size-[500px]">
        </div>
    </div>
    <nav
        class="fixed top-0 z-20 flex w-full justify-end gap-2 border-b border-slate-200/10 bg-slate-950/20 p-4 shadow-2xl backdrop-blur-md">
        <a href="{{ route('home.feed') }}" wire:navigate>
            <x-primary-colorless-button class="flex items-center justify-center gap-2">
                <x-heroicon-o-home class="h-4 w-4" />
                <span class="sr-only sm:not-sr-only">Feed</span>
            </x-primary-colorless-button>
        </a>
        @auth
            <a href="{{ route('profile.show', ['username' => auth()->user()->username]) }}" wire:navigate>
                <x-primary-button>Your Profile</x-primary-button>
            </a>
        @else
            <a href="{{ route('login') }}" wire:navigate>
                <x-primary-button>Log In</x-primary-button>
            </a>
            <a href="{{ route('register') }}" wire:navigate>
                <x-primary-button>Register</x-primary-button>
            </a>
        @endauth
    </nav>
    <main class="my-4 md:my-8 flex w-full flex-1 flex-col items-center justify-start gap-8 overflow-x-hidden p-4">
        <section class="mt-24 flex flex-col items-center">
            <h1 class="text-5xl md:text-6xl font-extrabold">
                503
            </h1>
        </section>
        <h2 class="mt-6 text-center font-mona text-2xl font-light md:text-3xl text-pink-500" style="font-stretch: 120%">
            {{ __('Service Unavailable') }}
        </h2>
    </main>
</x-about-layout>
