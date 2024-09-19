<x-about-layout>
    <div class="relative flex justify-center">
        <div class="absolute -top-48 -z-10 size-[400px] -rotate-45 rounded-full bg-gradient-to-br from-indigo-300 via-rose-200 to-green-600 opacity-70 blur-3xl lg:size-[500px]"></div>
    </div>
    <nav class="fixed top-0 z-20 flex w-full justify-end gap-2 border-b dark:border-slate-200/10 border-slate-900/10 dark:bg-slate-950/20 bg-slate-100/20 p-4 shadow-2xl backdrop-blur-md">
        <a
            href="{{ route('home.feed') }}"
            wire:navigate
        >
            <x-primary-colorless-button class="flex items-center justify-center gap-2 dark:border-white border-slate-900">
                <x-heroicon-o-home class="h-4 w-4" />
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
        <section class="mt-24 flex flex-col items-center">
            <a
                href="{{ route('about') }}"
                wire:navigate
            >
                <x-pinkary-logo class="z-10 w-72" />
            </a>

            <div
                class="mt-5 rounded-full bg-pink-500 bg-opacity-90 px-3 py-1.5 font-mona text-sm font-medium uppercase text-slate-900"
                style="font-stretch: 120%"
            >
                One link. All your socials.
            </div>
        </section>

        <h2
            class="mt-12 max-w-4xl text-center font-mona text-3xl font-light md:text-4xl"
            style="font-stretch: 120%"
        >
            Create a landing page for all your links and connect with like-minded people
            <span class="text-pink-500">without the noise</span>.
        </h2>

        <section class="mt-28 w-full max-w-2xl">
            <div class="grid w-full gap-4 md:grid-cols-2">
                <div class="rounded-2xl dark:border-t border-none dark:border-slate-800 dark:shadow-none shadow-sm dark:bg-slate-900 bg-slate-50 p-4 transition-colors md:aspect-video">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full dark:bg-slate-950 bg-slate-200">
                        <x-heroicon-o-bolt class="h-5 w-5" />
                    </div>

                    <h3>Create a profile</h3>
                    <p class="dark:text-slate-400 text-slate-500 text-sm">Choose a username, add a bio, and you're good to go.</p>
                </div>

                <div class="rounded-2xl dark:border-t border-none dark:border-slate-800 dark:shadow-none shadow-sm dark:bg-slate-900 bg-slate-50 p-4 md:aspect-video">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full dark:bg-slate-950 bg-slate-200">
                        <x-heroicon-o-link class="h-5 w-5" />
                    </div>

                    <h3>Share your links</h3>
                    <p class="dark:text-slate-400 text-slate-500 text-sm">Collect links of your social profiles, your work, and what matters to you.</p>
                </div>

                <div class="rounded-2xl dark:border-t border-none dark:border-slate-800 dark:shadow-none shadow-sm dark:bg-slate-900 bg-slate-50 p-4 md:aspect-video">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full dark:bg-slate-950 bg-slate-200">
                        <x-heroicon-o-chat-bubble-oval-left class="h-5 w-5" />
                    </div>

                    <h3>Ask and answer questions</h3>
                    <p class="dark:text-slate-400 text-slate-500 text-sm">Engage with the community in an open and friendly way.</p>
                </div>

                <div class="rounded-2xl dark:border-t border-none dark:border-slate-800 dark:shadow-none shadow-sm dark:bg-slate-900 bg-slate-50 p-4 md:aspect-video">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full dark:bg-slate-950 bg-slate-200">
                        <x-heroicon-o-globe-americas class="h-5 w-5" />
                    </div>

                    <h3>Discover</h3>
                    <p class="dark:text-slate-400 text-slate-500 text-sm">Keep an eye on the people you admire, and expand your circle.</p>
                </div>
            </div>
        </section>

        <section class="relative mt-20 grid w-full max-w-2xl grid-cols-1 place-items-center gap-2 md:grid-cols-2">
            <div class="absolute -left-20 top-0 h-56 w-56 rounded-full bg-gradient-to-r from-teal-500 via-transparent to-emerald-300 blur-3xl"></div>

            <div class="z-10 order-2 mt-10 w-full max-w-sm md:order-1 md:mt-0">
                <livewire:home.users />
            </div>

            <div class="order-1 flex cursor-pointer flex-col items-center justify-center transition-transform duration-700 sm:max-w-md md:order-2 md:-translate-y-10 md:translate-x-10 md:items-start md:hover:-translate-y-5 md:hover:translate-x-5">
                <h2 class="mb-2 w-full font-semibold">Lots of interesting profiles.</h2>
                <p class="text-slate-400">On pinkary you can find old friends or meet new interesting profiles.</p>
                <svg
                    class="ml-16 mt-10 hidden h-auto w-24 -rotate-45 md:block"
                    viewBox="0 0 251 81"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <g clip-path="url(#clip0_3_230)"><path d="M14.4435 26.0257C16.3478 34.2205 18.0405 42.2052 20.1564 51.2405C14.6551 50.6101 11.4813 47.2481 10.2118 43.4659C6.40317 32.1193 2.80615 20.5625 0.267089 8.79558C-1.21404 2.07164 3.65251 -1.50048 10.2118 0.600755C21.2144 3.96273 32.0054 7.95508 43.0081 11.7373C43.6428 11.9474 44.4892 12.1576 44.7008 12.5778C45.7587 14.0487 46.3935 15.7296 47.2398 17.4106C45.7587 18.041 44.2776 19.5119 43.0081 19.3017C38.5647 18.6714 34.3329 17.6208 30.1011 16.7803C27.7737 16.36 25.6577 15.7297 22.2723 16.7803C24.5998 19.3018 26.9273 22.0333 29.2548 24.5548C79.6129 74.5642 155.15 85.0703 217.781 51.2405C225.821 46.8279 233.227 41.5748 241.055 36.742C243.806 35.061 246.557 33.5901 249.307 31.9092C249.942 32.3294 250.365 32.9598 251 33.38C250.365 35.2711 250.154 37.7926 248.673 39.0533C244.018 43.4659 239.363 47.8785 234.073 51.6607C181.599 89.4829 108.601 90.9538 52.1064 54.8126C41.3154 47.8785 31.7938 39.0533 21.8492 31.0686C19.7333 29.3876 18.0406 27.4966 16.1363 25.6054C15.7131 25.3953 15.0783 25.6054 14.4435 26.0257Z" fill="currentColor"></path></g>
                </svg>
                <svg
                    class="-ml-12 mt-12 h-auto w-16 rotate-90 md:hidden"
                    viewBox="0 0 193 40"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path d="M173.928 21.1292C115.811 44.9386 58.751 45.774 0 26.1417C4.22669 21.7558 7.81938 23.4266 10.5667 24.262C31.7002 29.9011 53.4676 30.5277 75.0238 31.3631C106.09 32.6162 135.465 25.5151 164.207 14.0282C165.475 13.6104 166.532 12.775 169.068 11.1042C154.486 8.18025 139.903 13.1928 127.223 7.34485C127.435 6.50944 127.435 5.46513 127.646 4.62971C137.156 4.00315 146.877 3.37658 156.388 2.54117C165.898 1.70575 175.196 0.661517 184.706 0.0349538C191.68 -0.382755 194.639 2.9589 192.103 9.22453C188.933 17.3698 184.495 24.8886 180.48 32.6162C180.057 33.4516 179.423 34.4959 178.578 34.9136C176.253 35.749 173.928 35.9579 171.392 36.5845C170.97 34.4959 169.913 32.1985 170.124 30.3188C170.547 27.8126 172.026 25.724 173.928 21.1292Z" fill="currentColor"></path>
                </svg>
            </div>
        </section>

        <section class="relative mt-20 flex w-full max-w-3xl flex-col place-items-center gap-2 md:flex-row">
            <div class="absolute -right-20 top-0 -z-10 h-56 w-56 rotate-180 rounded-full bg-gradient-to-r from-teal-500 via-transparent to-emerald-300 blur-3xl"></div>

            <div class="flex cursor-pointer flex-col items-center justify-center text-left transition-transform duration-700 sm:max-w-md md:-translate-x-10 md:-translate-y-10 md:items-end md:text-right md:hover:-translate-x-5 md:hover:-translate-y-5">
                <h2 class="mb-2 w-full font-semibold">Any questions or thanks?</h2>
                <p class="w-full text-slate-400">Pinkary has a simple and direct way to communicate, even anonymously.</p>

                <svg
                    class="mr-16 mt-12 hidden h-auto w-20 rotate-45 md:block"
                    viewBox="0 0 220 87"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path d="M3.17247 25.8421C5.28745 28.1788 7.82542 30.0907 9.7289 32.6399C26.0142 55.3699 49.279 66.6287 75.0817 72.7892C83.9646 74.9135 94.1165 74.7011 103.211 72.7892C129.225 67.6909 152.913 56.8569 173.428 39.8625C179.35 34.9766 184.426 28.8161 188.656 21.8059C186.33 22.6556 183.792 23.5054 181.465 24.5675C174.697 27.5415 167.929 30.728 160.95 33.2772C157.989 34.3393 154.393 34.3393 151.009 34.1269C149.74 34.1269 147.837 32.215 147.625 30.9404C147.414 29.6658 148.683 27.3291 149.74 27.1167C167.718 21.5935 183.369 10.972 199.654 1.83746C205.364 -1.34899 208.96 -0.49927 211.498 5.23635C217.631 19.4692 220.381 34.5517 219.958 50.0592C219.958 50.484 219.112 51.1213 217.843 52.3959C205.364 47.7224 209.171 34.1269 203.038 23.2929C201.557 25.8421 200.5 27.9664 199.231 29.6658C172.582 62.5926 137.262 80.0118 96.2315 86.3848C90.0981 87.4469 83.3301 87.022 76.9852 85.9599C53.7205 81.9237 32.9937 72.1519 15.8623 55.7948C10.3634 50.484 6.34493 43.4738 2.32647 36.8885C0.634492 34.3393 0.634494 30.728 0 27.754C1.05749 27.1167 2.11498 26.4794 3.17247 25.8421Z" fill="currentColor"></path>
                </svg>
                <svg
                    class="-ml-12 mt-12 h-auto w-16 rotate-90 md:hidden"
                    viewBox="0 0 193 40"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path d="M173.928 21.1292C115.811 44.9386 58.751 45.774 0 26.1417C4.22669 21.7558 7.81938 23.4266 10.5667 24.262C31.7002 29.9011 53.4676 30.5277 75.0238 31.3631C106.09 32.6162 135.465 25.5151 164.207 14.0282C165.475 13.6104 166.532 12.775 169.068 11.1042C154.486 8.18025 139.903 13.1928 127.223 7.34485C127.435 6.50944 127.435 5.46513 127.646 4.62971C137.156 4.00315 146.877 3.37658 156.388 2.54117C165.898 1.70575 175.196 0.661517 184.706 0.0349538C191.68 -0.382755 194.639 2.9589 192.103 9.22453C188.933 17.3698 184.495 24.8886 180.48 32.6162C180.057 33.4516 179.423 34.4959 178.578 34.9136C176.253 35.749 173.928 35.9579 171.392 36.5845C170.97 34.4959 169.913 32.1985 170.124 30.3188C170.547 27.8126 172.026 25.724 173.928 21.1292Z" fill="currentColor"></path>
                </svg>
            </div>

            <div
                id="answers"
                class="mt-10 flex w-full flex-col gap-8 sm:max-w-md md:mt-0"
            >
                @if ($question = App\Models\Question::find('9b7fd5db-76a5-4533-910b-ad5590ed6124'))
                    <livewire:questions.show :questionId="$question->id" />
                @endif

                @if ($question = App\Models\Question::find('9b6e38c2-15db-4cd0-a0d7-3a300e877296'))
                    <livewire:questions.show :questionId="$question->id" />
                @endif

                @if ($question = App\Models\Question::find('9b899451-e4cc-494d-aa19-cf16e66f52f6'))
                    <livewire:questions.show :questionId="$question->id" />
                    <livewire:questions.show :questionId="$question->id" />
                @endif
            </div>
        </section>

        <div class="mt-28 flex max-w-2xl flex-col items-center gap-4 py-4 text-2xl font-light">
            <div class="animate-pulse">
                <x-heroicon-o-light-bulb class="h-8 w-8" />
            </div>

            <h2 class="text-center">
                Pinkary is now <a
                    href="https://github.com/pinkary-project/pinkary.com"
                    target="_blank"
                    class="underline hover:no-underline"
                >open-source</a>! You can help us by contributing or
                <a
                    href="https://github.com/sponsors/nunomaduro"
                    target="_blank"
                    class="underline hover:no-underline"
                >sponsoring the project on GitHub
                </a>
                .
            </h2>
        </div>

        <section class="mb-16 mt-40 flex flex-col items-center gap-8">
            @auth
                <h3
                    class="mb-4 w-full max-w-2xl text-center font-mona text-3xl font-light md:text-4xl"
                    style="font-stretch: 120%"
                >
                    Thank you for being part of this community!
                </h3>
            @else
                <h3
                    class="mb-4 w-full max-w-2xl text-center text-4xl font-light"
                    style="font-stretch: 120%"
                >
                    <a
                        href="{{ route('register') }}"
                        wire:navigate
                        class="underline hover:no-underline"
                        >Join</a
                    >
                    this growing community!
                </h3>
            @endauth

            <livewire:about-users-avatars lazy />
        </section>
    </main>
</x-about-layout>
