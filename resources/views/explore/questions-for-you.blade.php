<x-app-layout>
    <x-slot name="title">For you</x-slot>

    <div class="flex flex-col items-center justify-center">
        <div class="min-h-screen w-full max-w-md overflow-hidden shadow-md">
            <x-explore-menu></x-explore-menu>

            @auth
                <livewire:explore.questions-for-you :focus-input="true" />
            @else
                <div class="mb-4">Log in or sign up to access personalized content.</div>

                <a href="{{ route('login') }}" wire:navigate>
                    <x-primary-button>Log In</x-primary-button>
                </a>
                <a href="{{ route('register') }}" wire:navigate>
                    <x-primary-button>Register</x-primary-button>
                </a>
            @endauth
        </div>
    </div>
</x-app-layout>
