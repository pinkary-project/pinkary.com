<x-app-layout>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full overflow-hidden shadow-md sm:max-w-md sm:rounded-lg">
            <x-home-menu></x-home-menu>

            @auth
                <livewire:home.questions-for-you :focus-input="true" />
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
