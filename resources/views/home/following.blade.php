<x-app-layout>
    <header class="flex items-center justify-between border-b border-white/5 p-6 xl:px-8">
        <h1 class="font-medium text-white text-base/7">Feed</h1>
        <x-home-menu/>
    </header>

    @auth
        <livewire:questions.create :toId="auth()->id()"/>
        <livewire:home.questions-following :focus-input="true"/>
    @else
        <div class="p-6 xl:px-8">
            <div class="mb-4">Log in or sign up to access personalized content.</div>

            <a
                href="{{ route('login') }}"
                wire:navigate
            >
                <x-primary-button>Log In</x-primary-button>
            </a>
            <a
                href="{{ route('register') }}"
                wire:navigate
            >
                <x-primary-button>Register</x-primary-button>
            </a>
        </div>
    @endauth
</x-app-layout>
