<div class="mb-12 w-full text-slate-200">
    @auth
        <section class="mb-12 min-h-screen space-y-10">
            <livewire:questions.widget current-section="for-you" />
        </section>
    @else
        <div class="mb-4">
            <div class="mb-4">Log in or sign up to access personalized content.</div>

            <a href="{{ route('login') }}" wire:navigate>
                <x-primary-button>Log In</x-primary-button>
            </a>
            <a href="{{ route('register') }}" wire:navigate>
                <x-primary-button>Register</x-primary-button>
            </a>
        <div>
    @endauth
</div>
