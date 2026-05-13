<x-app-layout>
    <section class="overflow-hidden border-b border-r border-white/5 bg-black/10">
        <div class="flex flex-col gap-3 border-b border-white/5 px-6 py-6 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-[2rem] font-semibold tracking-tight text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="border-b border-white/5 px-6 py-6">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>

                <div>
                    <livewire:home.questions-following :focus-input="true"/>
                </div>
            @else
                <div class="m-6 border border-dashed border-white/10 bg-white/5 p-6 text-center">
                    <h3 class="font-mona text-xl font-semibold text-white">Sign in for a personalized feed</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-400">Log in or sign up to access personalized content. Following is already supported. You just need an account to see posts from the people you follow.</p>

                    <div class="mt-6 flex flex-wrap justify-center gap-3">
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
                </div>
            @endauth
        </div>
    </section>
</x-app-layout>
