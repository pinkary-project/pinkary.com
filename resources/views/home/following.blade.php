<x-app-layout>
    <section class="overflow-hidden rounded-[2.25rem] border border-slate-800/80 bg-[#07101f]/95 shadow-[0_0_0_1px_rgba(15,23,42,0.35)] ring-1 ring-white/5 backdrop-blur">
        <div class="flex flex-col gap-4 border-b border-slate-800 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <h2 class="text-[2.15rem] font-semibold tracking-tight text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-6 p-5 sm:p-6">
            @auth
                <livewire:questions.create :toId="auth()->id()" />

                <livewire:home.questions-following :focus-input="true"/>
            @else
                <div class="rounded-[1.75rem] border border-dashed border-slate-800 bg-[#071121]/95 p-6 text-center sm:p-8">
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
