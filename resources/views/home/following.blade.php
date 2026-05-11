<x-app-layout>
    <div class="space-y-6">
        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-4 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-5">
            <div class="space-y-4">
                <h2 class="font-mona text-2xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-3xl">
                    Updates from the people you follow.
                </h2>

                <x-home-menu></x-home-menu>
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-3 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-4">
            @auth
                <div class="mb-4 rounded-[1.75rem] border border-slate-200/70 bg-slate-50/80 p-3 dark:border-slate-800/70 dark:bg-slate-900/70 sm:p-4">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>

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
        </section>
    </div>
</x-app-layout>
