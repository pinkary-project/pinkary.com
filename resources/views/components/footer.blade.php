<footer class="border-t border-slate-200/70 bg-white/40 backdrop-blur dark:border-slate-800/70 dark:bg-slate-950/40">
    <div class="mx-auto flex w-full max-w-[82rem] flex-col gap-6 px-4 py-8 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <div>
            <p class="font-mona text-lg font-semibold text-slate-950 dark:text-white">{{ config('app.name') }}</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">One link. All your socials.</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ $version }}</p>
        </div>

        <nav
            class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm"
            aria-label="Footer"
        >
            <a
                href="{{ route('changelog') }}"
                class="text-slate-500 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >Changelog</a>
            <a
                href="{{ route('terms') }}"
                class="text-slate-500 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >Terms</a>
            <a
                href="{{ route('privacy') }}"
                class="text-slate-500 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >Privacy Policy</a>
            <a
                href="{{ route('support') }}"
                class="text-slate-500 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >Support</a>
            <a
                href="{{ route('verified') }}"
                class="text-slate-500 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >Verified</a>
            <a
                href="{{ route('brand.resources') }}"
                class="text-slate-500 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >Brand</a>
        </nav>

        <div class="flex items-center gap-4">
            <a
                href="https://twitter.com/PinkaryProject"
                target="_blank"
                class="text-slate-500 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >
                <span class="sr-only">X</span>

                <x-icons.twitter-x class="h-5 w-5" />
            </a>

            <a
                href="https://github.com/pinkary-project"
                target="_blank"
                class="text-slate-500 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >
                <span class="sr-only">GitHub</span>

                <x-icons.github class="h-5 w-5" />
            </a>
        </div>
    </div>

    <livewire:views.create />
</footer>
