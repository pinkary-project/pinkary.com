<x-welcome-layout>
    <div class="absolute -top-48 -z-10 h-[30vw] w-[30vw] -rotate-45 rounded-full bg-gradient-to-br from-indigo-300 via-rose-200 to-green-600 opacity-70 blur-3xl">
    </div>
    <nav class="sticky top-0 z-20 flex w-full justify-end gap-2 border-b border-slate-200 border-opacity-20 bg-gray-950/20 p-4 backdrop-blur-md">
        @auth
            <a href="{{ route('profile.show', ['user' => auth()->user()->username]) }}" wire:navigate>
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
        <section class="mt-24 flex flex-col items-center">
            <a href="{{ route('welcome') }}" wire:navigate>
                <figure>
                    <img class="z-10 w-72" src="{{ asset('img/home-logo.png') }}" alt="Pinkary logo." />
                </figure>
            </a>

            <div
                class="rounded-[2.75rem] bg-pink-300 bg-opacity-90 px-3 pb-1.5 pt-1.5 text-sm font-medium uppercase text-slate-900"
                style="font-stretch: 120%"
            >
                One link. All your socials.
            </div>
        </section>

        <h2 class="mt-12 max-w-4xl text-center text-3xl font-light md:text-4xl" style="font-stretch: 120%">
            Create a landing page for all your links and connect with like-minded people
            <span class="underline decoration-slate-600 decoration-wavy">without the noise</span>
            .
        </h2>

        <section class="mt-28 w-full max-w-2xl">
            <div class="grid w-full gap-2 md:grid-cols-2">
                <div class="rounded-2xl border-t border-slate-800 bg-slate-900 p-4 transition-colors md:aspect-video">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-950">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>
                    </div>

                    <h3>Create a profile</h3>
                    <p class="text-slate-400">Choose a username, add a bio, and you're good to go.</p>
                </div>

                <div class="rounded-2xl border-t border-slate-800 bg-slate-900 p-4 md:aspect-video">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-950">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                    </div>

                    <h3>Share your links</h3>
                    <p class="text-slate-400">
                        Collect links of your social profiles, your work, and what matters to you.
                    </p>
                </div>

                <div class="rounded-2xl border-t border-slate-800 bg-slate-900 p-4 md:aspect-video">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-950">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" /></svg>
                    </div>

                    <h3>Ask and answer questions</h3>
                    <p class="text-slate-400">Engage with the community in an open and friendly way.</p>
                </div>

                <div class="rounded-2xl border-t border-slate-800 bg-slate-900 p-4 md:aspect-video">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-950">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m6.115 5.19.319 1.913A6 6 0 0 0 8.11 10.36L9.75 12l-.387.775c-.217.433-.132.956.21 1.298l1.348 1.348c.21.21.329.497.329.795v1.089c0 .426.24.815.622 1.006l.153.076c.433.217.956.132 1.298-.21l.723-.723a8.7 8.7 0 0 0 2.288-4.042 1.087 1.087 0 0 0-.358-1.099l-1.33-1.108c-.251-.21-.582-.299-.905-.245l-1.17.195a1.125 1.125 0 0 1-.98-.314l-.295-.295a1.125 1.125 0 0 1 0-1.591l.13-.132a1.125 1.125 0 0 1 1.3-.21l.603.302a.809.809 0 0 0 1.086-1.086L14.25 7.5l1.256-.837a4.5 4.5 0 0 0 1.528-1.732l.146-.292M6.115 5.19A9 9 0 1 0 17.18 4.64M6.115 5.19A8.965 8.965 0 0 1 12 3c1.929 0 3.716.607 5.18 1.64" /></svg>
                    </div>

                    <h3>Discover</h3>
                    <p class="text-slate-400">keep an eye on the people you admire, and expand your circle.</p>
                </div>
            </div>
        </section>

        <section class="relative mt-20 grid w-full max-w-2xl grid-cols-1 place-items-center gap-2 md:grid-cols-2">
            <div class="absolute -left-20 top-0 h-56 w-56 rounded-full bg-gradient-to-r from-teal-500 via-transparent to-emerald-300 blur-3xl">
            </div>

            <div class="z-10 order-2 mt-10 w-full max-w-sm md:order-1 md:mt-0">
                <livewire:users.index />
            </div>

            <div class="order-1 flex cursor-pointer flex-col items-center justify-center transition-transform duration-700 sm:max-w-md md:order-2 md:-translate-y-10 md:translate-x-10 md:items-start md:hover:-translate-y-5 md:hover:translate-x-5">
                <h2 class="mb-2 w-full font-semibold">Lots of interesting profiles.</h2>
                <p class="text-slate-400">On pinkary you can find old friends or meet new interesting profiles.</p>
                <svg class="ml-16 mt-10 hidden h-auto w-24 -rotate-45 md:block" viewBox="0 0 251 81" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_3_230)"><path d="M14.4435 26.0257C16.3478 34.2205 18.0405 42.2052 20.1564 51.2405C14.6551 50.6101 11.4813 47.2481 10.2118 43.4659C6.40317 32.1193 2.80615 20.5625 0.267089 8.79558C-1.21404 2.07164 3.65251 -1.50048 10.2118 0.600755C21.2144 3.96273 32.0054 7.95508 43.0081 11.7373C43.6428 11.9474 44.4892 12.1576 44.7008 12.5778C45.7587 14.0487 46.3935 15.7296 47.2398 17.4106C45.7587 18.041 44.2776 19.5119 43.0081 19.3017C38.5647 18.6714 34.3329 17.6208 30.1011 16.7803C27.7737 16.36 25.6577 15.7297 22.2723 16.7803C24.5998 19.3018 26.9273 22.0333 29.2548 24.5548C79.6129 74.5642 155.15 85.0703 217.781 51.2405C225.821 46.8279 233.227 41.5748 241.055 36.742C243.806 35.061 246.557 33.5901 249.307 31.9092C249.942 32.3294 250.365 32.9598 251 33.38C250.365 35.2711 250.154 37.7926 248.673 39.0533C244.018 43.4659 239.363 47.8785 234.073 51.6607C181.599 89.4829 108.601 90.9538 52.1064 54.8126C41.3154 47.8785 31.7938 39.0533 21.8492 31.0686C19.7333 29.3876 18.0406 27.4966 16.1363 25.6054C15.7131 25.3953 15.0783 25.6054 14.4435 26.0257Z" fill="currentColor" ></path></g></svg>
                <svg class="-ml-12 mt-12 h-auto w-16 rotate-90 md:hidden" viewBox="0 0 193 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M173.928 21.1292C115.811 44.9386 58.751 45.774 0 26.1417C4.22669 21.7558 7.81938 23.4266 10.5667 24.262C31.7002 29.9011 53.4676 30.5277 75.0238 31.3631C106.09 32.6162 135.465 25.5151 164.207 14.0282C165.475 13.6104 166.532 12.775 169.068 11.1042C154.486 8.18025 139.903 13.1928 127.223 7.34485C127.435 6.50944 127.435 5.46513 127.646 4.62971C137.156 4.00315 146.877 3.37658 156.388 2.54117C165.898 1.70575 175.196 0.661517 184.706 0.0349538C191.68 -0.382755 194.639 2.9589 192.103 9.22453C188.933 17.3698 184.495 24.8886 180.48 32.6162C180.057 33.4516 179.423 34.4959 178.578 34.9136C176.253 35.749 173.928 35.9579 171.392 36.5845C170.97 34.4959 169.913 32.1985 170.124 30.3188C170.547 27.8126 172.026 25.724 173.928 21.1292Z" fill="currentColor" ></path></svg>
            </div>
        </section>

        <section class="relative mt-20 flex w-full max-w-3xl flex-col place-items-center gap-2 md:flex-row">
            <div class="absolute -right-20 top-0 -z-10 h-56 w-56 rotate-180 rounded-full bg-gradient-to-r from-teal-500 via-transparent to-emerald-300 blur-3xl">
            </div>

            <div class="flex cursor-pointer flex-col items-center justify-center text-left transition-transform duration-700 sm:max-w-md md:-translate-x-10 md:-translate-y-10 md:items-end md:text-right md:hover:-translate-x-5 md:hover:-translate-y-5">
                <h2 class="mb-2 w-full font-semibold">Any questions or thanks?</h2>
                <p class="w-full text-slate-400">
                    Pinkary has a simple and direct way to communicate, even anonymously.
                </p>

                <svg class="mr-16 mt-12 hidden h-auto w-20 rotate-45 md:block" viewBox="0 0 220 87" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.17247 25.8421C5.28745 28.1788 7.82542 30.0907 9.7289 32.6399C26.0142 55.3699 49.279 66.6287 75.0817 72.7892C83.9646 74.9135 94.1165 74.7011 103.211 72.7892C129.225 67.6909 152.913 56.8569 173.428 39.8625C179.35 34.9766 184.426 28.8161 188.656 21.8059C186.33 22.6556 183.792 23.5054 181.465 24.5675C174.697 27.5415 167.929 30.728 160.95 33.2772C157.989 34.3393 154.393 34.3393 151.009 34.1269C149.74 34.1269 147.837 32.215 147.625 30.9404C147.414 29.6658 148.683 27.3291 149.74 27.1167C167.718 21.5935 183.369 10.972 199.654 1.83746C205.364 -1.34899 208.96 -0.49927 211.498 5.23635C217.631 19.4692 220.381 34.5517 219.958 50.0592C219.958 50.484 219.112 51.1213 217.843 52.3959C205.364 47.7224 209.171 34.1269 203.038 23.2929C201.557 25.8421 200.5 27.9664 199.231 29.6658C172.582 62.5926 137.262 80.0118 96.2315 86.3848C90.0981 87.4469 83.3301 87.022 76.9852 85.9599C53.7205 81.9237 32.9937 72.1519 15.8623 55.7948C10.3634 50.484 6.34493 43.4738 2.32647 36.8885C0.634492 34.3393 0.634494 30.728 0 27.754C1.05749 27.1167 2.11498 26.4794 3.17247 25.8421Z" fill="currentColor" ></path></svg>

                <svg class="-ml-12 mt-12 h-auto w-16 rotate-90 md:hidden" viewBox="0 0 193 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M173.928 21.1292C115.811 44.9386 58.751 45.774 0 26.1417C4.22669 21.7558 7.81938 23.4266 10.5667 24.262C31.7002 29.9011 53.4676 30.5277 75.0238 31.3631C106.09 32.6162 135.465 25.5151 164.207 14.0282C165.475 13.6104 166.532 12.775 169.068 11.1042C154.486 8.18025 139.903 13.1928 127.223 7.34485C127.435 6.50944 127.435 5.46513 127.646 4.62971C137.156 4.00315 146.877 3.37658 156.388 2.54117C165.898 1.70575 175.196 0.661517 184.706 0.0349538C191.68 -0.382755 194.639 2.9589 192.103 9.22453C188.933 17.3698 184.495 24.8886 180.48 32.6162C180.057 33.4516 179.423 34.4959 178.578 34.9136C176.253 35.749 173.928 35.9579 171.392 36.5845C170.97 34.4959 169.913 32.1985 170.124 30.3188C170.547 27.8126 172.026 25.724 173.928 21.1292Z" fill="currentColor" ></path></svg>
            </div>

            <div id="answers" class="mt-10 flex w-full flex-col gap-8 sm:max-w-md md:mt-0">
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
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor" class="h-8 w-8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" /></svg>
            </div>

            <h2 class="text-center">
                Pinkary is now open-source! You can get early access by
                <a href="https://github.com/sponsors/nunomaduro" target="_blank" class="underline">
                    sponsoring the project on GitHub
                </a>
                .
            </h2>
        </div>

        <section class="mb-16 mt-40 flex flex-col items-center gap-8">
            @auth
                <h3
                    class="mb-4 w-full max-w-2xl text-center text-3xl font-light md:text-4xl"
                    style="font-stretch: 120%"
                >
                    Thank you for being part of this community!
                </h3>
            @else
                <h3 class="mb-4 w-full max-w-2xl text-center text-4xl font-light" style="font-stretch: 120%">
                    <a href="{{ route('register') }}" wire:navigate class="underline">Join</a>
                    this growing community!
                </h3>
            @endauth

            <livewire:welcome-users-avatars lazy />
        </section>
    </main>
</x-welcome-layout>
