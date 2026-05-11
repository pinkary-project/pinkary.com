<x-app-layout>
    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/85 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/85 dark:shadow-black/20">
        <div class="flex flex-col gap-4 border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/70 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <h2 class="font-mona text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-4 p-4 sm:p-6">
            @auth
                <livewire:questions.create :toId="auth()->id()" />

                <livewire:home.questions-following :focus-input="true"/>
            @else
                <div class="rounded-[1.75rem] border border-dashed border-slate-300/80 bg-slate-50/70 p-6 text-center dark:border-slate-700/80 dark:bg-slate-900/50 sm:p-8">
                    <h3 class="font-mona text-xl font-semibold text-slate-950 dark:text-white">Sign in for a personalized feed</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">Log in or sign up to access personalized content. Following is already supported. You just need an account to see posts from the people you follow.</p>

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
