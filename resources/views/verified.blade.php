<x-about-layout>
    <div class="relative flex justify-center">
        <div
            class="absolute -top-48 -z-10 size-[400px] -rotate-45 rounded-full bg-gradient-to-br from-indigo-300 via-rose-200 to-green-600 opacity-70 blur-3xl lg:size-[500px]"></div>
    </div>

    <nav
        class="fixed top-0 z-20 flex w-full justify-end gap-2 border-b dark:border-slate-200/10 border-slate-900/10 dark:bg-slate-950/20 bg-slate-100/20 p-4 shadow-2xl backdrop-blur-md">
        <a
            href="{{ route('home.feed') }}"
            wire:navigate
        >
            <x-primary-colorless-button
                class="flex items-center justify-center gap-2 dark:border-white border-slate-900">
                <x-heroicon-o-home class="h-4 w-4"/>
                <span class="sr-only sm:not-sr-only">Feed</span>
            </x-primary-colorless-button>
        </a>
        @auth
            <a
                href="{{ route('profile.show', ['username' => auth()->user()->username]) }}"
                wire:navigate
            >
                <x-primary-button>Your Profile</x-primary-button>
            </a>
        @else
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
        @endauth
    </nav>

    <main class="my-8 flex w-full flex-1 flex-col items-center justify-center gap-8 overflow-x-hidden p-4 pb-12">
        <section class="mt-24 flex flex-col items-center w-full">
            <div class="relative">
                <a
                    href="{{ route('about') }}"
                    wire:navigate
                >
                    <x-pinkary-logo class="z-10 w-72"/>
                    <x-icons.verified
                        :color="'blue-500'"
                        class="ml-1 mt-0.5 h-10 w-10 absolute -top-4 -right-4"
                    />
                </a>
            </div>


            <div
                class="mt-5 rounded-full bg-pink-500 bg-opacity-90 px-3 py-1.5 font-mona text-sm font-medium uppercase text-slate-900"
                style="font-stretch: 120%"
            >
                Become a trusted user
            </div>
        </section>

        @if(auth()->user())
            <section class="mt-28 w-full max-w-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.verified-form', ['user' => auth()->user()])
                </div>
            </section>
        @endif

        <section class="mt-10 w-full max-w-2xl">
            <div class="grid w-full gap-4 md:grid-cols-2">
                <div
                    class="rounded-2xl dark:border-t border-none dark:border-slate-800 dark:shadow-none shadow-sm dark:bg-slate-900 bg-slate-50 p-4 transition-colors md:aspect-video">
                    <div
                        class="mb-3 flex h-12 w-12 items-center justify-center rounded-full dark:bg-slate-950 bg-slate-200">
                        <x-heroicon-o-wrench-screwdriver class="h-5 w-5"/>
                    </div>

                    <h3>Support the Project</h3>
                    <p class="dark:text-slate-400 text-slate-500 text-sm">By becoming verified, you directly support the
                        creator and help maintain and improve Pinkary.</p>
                </div>

                <div
                    class="rounded-2xl dark:border-t border-none dark:border-slate-800 dark:shadow-none shadow-sm dark:bg-slate-900 bg-slate-50 p-4 md:aspect-video">
                    <div
                        class="mb-3 flex h-12 w-12 items-center justify-center rounded-full dark:bg-slate-950 bg-slate-200">
                        <x-heroicon-o-user-group class="h-5 w-5"/>
                    </div>

                    <h3>Community Recognition</h3>
                    <p class="dark:text-slate-400 text-slate-500 text-sm">A verification badge highlights your
                        commitment and support within the Pinkary community.</p>
                </div>

                <div
                    class="rounded-2xl dark:border-t border-none dark:border-slate-800 dark:shadow-none shadow-sm dark:bg-slate-900 bg-slate-50 p-4 md:aspect-video">
                    <div
                        class="mb-3 flex h-12 w-12 items-center justify-center rounded-full dark:bg-slate-950 bg-slate-200">
                        <x-heroicon-o-heart class="h-5 w-5"/>
                    </div>

                    <h3>Help Pinkary Grow</h3>
                    <p class="dark:text-slate-400 text-slate-500 text-sm">Your verification contributes to Pinkary’s
                        development and future improvements.</p>
                </div>

                <div
                    class="rounded-2xl dark:border-t border-none dark:border-slate-800 dark:shadow-none shadow-sm dark:bg-slate-900 bg-slate-50 p-4 md:aspect-video">
                    <div
                        class="mb-3 flex h-12 w-12 items-center justify-center rounded-full dark:bg-slate-950 bg-slate-200">
                        <x-heroicon-o-fire class="h-5 w-5"/>
                    </div>

                    <h3>Encourage Open-Source Contributions</h3>
                    <p class="dark:text-slate-400 text-slate-500 text-sm"> Supporting the creator promotes open-source
                        values and collaboration in projects like Pinkary.</p>
                </div>
            </div>
        </section>

        <section class="mt-10 w-full max-w-2xl">
            <h2 class="text-lg font-medium dark:text-slate-400 text-slate-600">Frequently Asked Questions</h2>

            <!-- 1 -->
            <div x-data="{ expanded: false }" :class="expanded ? 'shadow-2xl' : 'shadow-sm hover:shadow-md'" class="relative transition-all border border-transparent rounded-xl dark:bg-slate-900 bg-slate-50 mt-5 select-none">
                <div @click="expanded = !expanded" :aria-expanded="expanded" class="w-full p-4 text-left cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="tracking-wide">What does verification mean on Pinkary?</span>
                        <span :class="expanded ? '-rotate-180' : ''" class="transition-transform transform fill-current">
                            <svg class="size-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </span>
                    </div>
                </div>

                <div x-cloak x-show="expanded" x-collapse class="relative overflow-hidden transition-all">
                    <div class="px-4 pb-4 text-gray-600 dark:text-gray-400">
                        Verification on Pinkary gives you a badge next to your name, symbolizing your support for the platform’s development. It helps recognize your contribution as an engaged and supportive member of the community.
                    </div>
                </div>
            </div>
            <!-- End 1 -->

            <!-- 2 -->
            <div x-data="{ expanded: false }" :class="expanded ? 'shadow-2xl' : 'shadow-sm hover:shadow-md'" class="relative transition-all border border-transparent rounded-xl dark:bg-slate-900 bg-slate-50 mt-5 select-none">
                <div @click="expanded = !expanded" :aria-expanded="expanded" class="w-full p-4 text-left cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="tracking-wide">How do I become verified on Pinkary?</span>
                        <span :class="expanded ? '-rotate-180' : ''" class="transition-transform transform fill-current">
                            <svg class="size-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </span>
                    </div>
                </div>

                <div x-cloak x-show="expanded" x-collapse class="relative overflow-hidden transition-all">
                    <div class="px-4 pb-4 text-gray-600 dark:text-gray-400">
                        To get verified, connect your GitHub account to Pinkary, and sponsor the creator with at least $9/month via GitHub Sponsors. Once sponsored, the verification badge will appear automatically on your profile.
                    </div>
                </div>
            </div>
            <!-- End 2 -->

            <!-- 3 -->
            <div x-data="{ expanded: false }" :class="expanded ? 'shadow-2xl' : 'shadow-sm hover:shadow-md'" class="relative transition-all border border-transparent rounded-xl dark:bg-slate-900 bg-slate-50 mt-5 select-none">
                <div @click="expanded = !expanded" :aria-expanded="expanded" class="w-full p-4 text-left cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="tracking-wide">Why should I get verified?</span>
                        <span :class="expanded ? '-rotate-180' : ''" class="transition-transform transform fill-current">
                            <svg class="size-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </span>
                    </div>
                </div>

                <div x-cloak x-show="expanded" x-collapse class="relative overflow-hidden transition-all">
                    <div class="px-4 pb-4 text-gray-600 dark:text-gray-400">
                        By getting verified, you support the ongoing development of Pinkary, help improve the platform, and promote open-source contributions. The verification badge also highlights your commitment to the community
                    </div>
                </div>
            </div>
            <!-- End 3 -->
        </section>
    </main>
</x-about-layout>
