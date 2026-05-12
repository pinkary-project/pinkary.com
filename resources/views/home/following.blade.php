<x-app-layout>
    <section class="overflow-hidden border border-slate-800/55 lg:border-t-0 bg-[#07101f]/95">
        <div class="flex flex-col gap-2 border-b border-slate-800/55 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between sm:px-4">
            <h2 class="text-[2rem] font-semibold tracking-tight text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="px-2 py-2.5 sm:px-3">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>

                <div class="px-0 pb-2 sm:pb-3">
                    <livewire:home.questions-following :focus-input="true"/>
                </div>
            @else
                <div class="m-3 border border-dashed border-slate-800/55 bg-[#071121]/95 p-5 text-center sm:m-4 sm:p-6">
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
