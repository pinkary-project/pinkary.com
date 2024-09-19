<x-app-layout>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-md overflow-hidden rounded-lg px-2 dark:shadow-md sm:px-0">
            <x-home-menu></x-home-menu>

            @auth
                <livewire:home.questions-following :focus-input="true"/>
            @else
                <div class="mb-4">
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
        </div>
    </div>
</x-app-layout>
