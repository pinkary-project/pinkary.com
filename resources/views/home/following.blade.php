<x-app-layout>
    <section class="border-x border-b border-slate-200 dark:border-slate-700/50 bg-white/80 dark:bg-[#07101f]/95">
        <div class="sticky -top-1 z-30 flex flex-col gap-2 border-b border-slate-200/70 bg-white px-6 py-4 dark:border-slate-800/30 dark:bg-[#07101f] sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:bg-white/90 sm:py-6 sm:backdrop-blur dark:sm:bg-[#07101f]/95">
            <h2 class="hidden text-[2rem] font-semibold tracking-tight text-slate-950 dark:text-white sm:block">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/30">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>

                <div>
                    <livewire:home.questions-following :focus-input="true"/>
                </div>
            @else
                <div class="m-6 border border-dashed border-slate-300/80 bg-slate-50/70 p-6 text-center dark:border-slate-800/50 dark:bg-[#0b1324]">
                    <h3 class="font-mona text-xl font-semibold text-slate-950 dark:text-white">Sign in for a personalized feed</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">Log in or sign up to access personalized content. Following is already supported. You just need an account to see posts from the people you follow.</p>

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
