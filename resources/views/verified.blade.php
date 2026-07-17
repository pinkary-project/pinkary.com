<x-about-layout>
    <div class="relative flex justify-center">
        <div class="absolute -top-48 -z-10 size-[400px] -rotate-45 rounded-full bg-linear-to-br from-indigo-300 via-rose-200 to-green-600 opacity-70 blur-3xl lg:size-[500px]"></div>
    </div>

    <nav class="fixed top-0 z-20 flex w-full justify-end gap-2 border-b border-slate-900/10 bg-slate-100/20 p-4 shadow-2xl backdrop-blur-md dark:border-slate-200/10 dark:bg-slate-950/20">
        <a href="{{ route('home.feed') }}" wire:navigate>
            <x-primary-colorless-button class="flex items-center justify-center gap-2 border-slate-900 dark:border-white">
                <x-heroicon-o-home class="h-4 w-4" />
                <span class="sr-only sm:not-sr-only">Feed</span>
            </x-primary-colorless-button>
        </a>
        @auth
            <a href="{{ route('profile.show', ['username' => auth()->user()->username]) }}" wire:navigate>
                <x-primary-button>Your Profile</x-primary-button>
            </a>
        @else
            <a href="{{ route('login') }}" wire:navigate>
                <x-primary-button>Log In</x-primary-button>
            </a>
            <a href="{{ route('register') }}" wire:navigate>
                <x-primary-button>Register</x-primary-button>
            </a>
        @endauth
    </nav>

    <main class="my-8 flex w-full flex-1 flex-col items-center justify-center gap-8 overflow-x-hidden p-4 pb-12">
        <section class="mt-24 flex w-full flex-col items-center">
            <div class="relative">
                <a href="{{ route('about') }}" wire:navigate>
                    <x-pinkary-logo class="z-10 w-72" />
                    <x-icons.verified :color="'blue-500'" class="absolute -top-4 -right-4 mt-0.5 ml-1 h-10 w-10" />
                </a>
            </div>

            <div
                class="bg-opacity-90 font-mona mt-5 rounded-full bg-pink-500 px-3 py-1.5 text-sm font-medium text-slate-900 uppercase"
                style="font-stretch: 120%"
            >
                Become a trusted user
            </div>
        </section>

        @if (auth()->user())
            <section class="mt-28 w-full max-w-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.verified-form', ['user' => auth()->user()])
                </div>
            </section>
        @endif

        <section class="mt-10 w-full max-w-2xl">
            <div class="grid w-full gap-4 md:grid-cols-2">
                <div class="rounded-2xl border-none bg-slate-50 p-4 shadow-sm transition-colors md:aspect-video dark:border-t dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 dark:bg-slate-950">
                        <x-heroicon-o-wrench-screwdriver class="h-5 w-5" />
                    </div>

                    <h3>Support the Project</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        By becoming verified, you directly support the creator and help maintain and improve Pinkary.
                    </p>
                </div>

                <div class="rounded-2xl border-none bg-slate-50 p-4 shadow-sm md:aspect-video dark:border-t dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 dark:bg-slate-950">
                        <x-heroicon-o-user-group class="h-5 w-5" />
                    </div>

                    <h3>Community Recognition</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        A verification badge highlights your commitment and support within the Pinkary community.
                    </p>
                </div>

                <div class="rounded-2xl border-none bg-slate-50 p-4 shadow-sm md:aspect-video dark:border-t dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 dark:bg-slate-950">
                        <x-heroicon-o-heart class="h-5 w-5" />
                    </div>

                    <h3>Help Pinkary Grow</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Your verification contributes to Pinkary’s development and future improvements.
                    </p>
                </div>

                <div class="rounded-2xl border-none bg-slate-50 p-4 shadow-sm md:aspect-video dark:border-t dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 dark:bg-slate-950">
                        <x-heroicon-o-fire class="h-5 w-5" />
                    </div>

                    <h3>Encourage Open-Source Contributions</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Supporting the creator promotes open-source values and collaboration in projects like Pinkary.
                    </p>
                </div>
            </div>
        </section>

        <section class="mt-10 w-full max-w-2xl">
            <h2 class="text-lg font-medium text-slate-600 dark:text-slate-400">Frequently Asked Questions</h2>

            <!-- 1 -->
            <div
                x-data="{ expanded: false }"
                :class="expanded ? 'shadow-2xl' : 'shadow-sm hover:shadow-md'"
                class="relative mt-5 rounded-xl border border-transparent bg-slate-50 transition-all select-none dark:bg-slate-900"
            >
                <div
                    @click="expanded = ! expanded"
                    :aria-expanded="expanded"
                    class="w-full cursor-pointer p-4 text-left"
                >
                    <div class="flex items-center justify-between">
                        <span class="tracking-wide">What does verification mean on Pinkary?</span>
                        <span
                            :class="expanded ? '-rotate-180' : ''"
                            class="transform fill-current transition-transform"
                        >
                            <svg class="size-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
                        </span>
                    </div>
                </div>

                <div x-cloak x-show="expanded" x-collapse class="relative overflow-hidden transition-all">
                    <div class="px-4 pb-4 text-gray-600 dark:text-gray-400">
                        Verification on Pinkary gives you a badge next to your name, symbolizing your support for the
                        platform’s development. It helps recognize your contribution as an engaged and supportive member
                        of the community.
                    </div>
                </div>
            </div>
            <!-- End 1 -->

            <!-- 2 -->
            <div
                x-data="{ expanded: false }"
                :class="expanded ? 'shadow-2xl' : 'shadow-sm hover:shadow-md'"
                class="relative mt-5 rounded-xl border border-transparent bg-slate-50 transition-all select-none dark:bg-slate-900"
            >
                <div
                    @click="expanded = ! expanded"
                    :aria-expanded="expanded"
                    class="w-full cursor-pointer p-4 text-left"
                >
                    <div class="flex items-center justify-between">
                        <span class="tracking-wide">How do I become verified on Pinkary?</span>
                        <span
                            :class="expanded ? '-rotate-180' : ''"
                            class="transform fill-current transition-transform"
                        >
                            <svg class="size-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
                        </span>
                    </div>
                </div>

                <div x-cloak x-show="expanded" x-collapse class="relative overflow-hidden transition-all">
                    <div class="px-4 pb-4 text-gray-600 dark:text-gray-400">
                        To get verified, connect your GitHub account to Pinkary, and sponsor the creator with at least
                        $9/month via GitHub Sponsors. Once sponsored, the verification badge will appear automatically
                        on your profile.
                    </div>
                </div>
            </div>
            <!-- End 2 -->

            <!-- 3 -->
            <div
                x-data="{ expanded: false }"
                :class="expanded ? 'shadow-2xl' : 'shadow-sm hover:shadow-md'"
                class="relative mt-5 rounded-xl border border-transparent bg-slate-50 transition-all select-none dark:bg-slate-900"
            >
                <div
                    @click="expanded = ! expanded"
                    :aria-expanded="expanded"
                    class="w-full cursor-pointer p-4 text-left"
                >
                    <div class="flex items-center justify-between">
                        <span class="tracking-wide">Why should I get verified?</span>
                        <span
                            :class="expanded ? '-rotate-180' : ''"
                            class="transform fill-current transition-transform"
                        >
                            <svg class="size-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
                        </span>
                    </div>
                </div>

                <div x-cloak x-show="expanded" x-collapse class="relative overflow-hidden transition-all">
                    <div class="px-4 pb-4 text-gray-600 dark:text-gray-400">
                        By getting verified, you support the ongoing development of Pinkary, help improve the platform,
                        and promote open-source contributions. The verification badge also highlights your commitment to
                        the community
                    </div>
                </div>
            </div>
            <!-- End 3 -->
        </section>
    </main>
</x-about-layout>
