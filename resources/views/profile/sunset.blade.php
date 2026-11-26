<x-about-layout>
    <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col items-center justify-center px-6 py-16 text-center">
        <a href="{{ route('about') }}" wire:navigate>
            <x-pinkary-logo class="w-48" />
        </a>

        <div class="mt-10 w-full border border-slate-200/70 bg-white/85 p-8 shadow-sm dark:border-slate-800/30 dark:bg-[#07101f]/90">
            <p class="text-sm font-semibold tracking-wide text-pink-600 uppercase dark:text-pink-400">
                {{ __('Pinkary has shut down') }}
            </p>

            <h1 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-white">
                {{ __('@:username did not leave a redirect', ['username' => $user->username]) }}
            </h1>

            <p class="mt-4 text-slate-600 dark:text-slate-300">
                {{ __('Pinkary went offline on :date. This profile did not configure a destination to visit instead.', ['date' => $sunsetDate]) }}
            </p>

            <p class="mt-4 text-slate-600 dark:text-slate-300">
                {{ __('This page remains available so old profile links do not break.') }}
            </p>

            <a
                href="https://nunomaduro.com/posts/sunsetting-pinkary"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-6 inline-block font-semibold text-pink-600 underline underline-offset-2 hover:text-pink-700 dark:text-pink-400 dark:hover:text-pink-300"
            >
                {{ __('Read the shutdown announcement') }}
            </a>
        </div>
    </main>
</x-about-layout>
